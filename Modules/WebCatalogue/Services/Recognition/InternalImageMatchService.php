<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Support\Facades\Storage;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\ResourceFingerprint;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\VisualRecognitionCapture;
use Modules\WebCatalogue\Models\VisualRecognitionMatch;
use Modules\WebCatalogue\Models\VisualRecognitionSession;

class InternalImageMatchService
{
    /**
     * v2.26: composite matching.
     *
     * The previous matcher relied mostly on pHash. That proved the pipeline, but was still too sensitive
     * to background and light. This version creates a reusable image fingerprint per resource and compares
     * the captured image against every product image using a weighted score:
     * pHash + edge/shape hash + colour histogram.
     */
    public function matchSession(VisualRecognitionSession $session, Store $store, int $limit = 5): array
    {
        $capture = $session->captures()
            ->where('capture_type', 'object_photo')
            ->latest()
            ->first();

        if (!$capture || !$capture->file_path) {
            $session->update(['status' => 'capture_missing']);
            return $this->emptyResult('No capture image available for matching.');
        }

        $captureProfile = $this->profileFromPublicPath($capture->file_path);
        if ($captureProfile === null) {
            $session->update([
                'status' => 'match_failed',
                'metadata' => array_merge($session->metadata ?: [], [
                    'match_error' => 'Could not create image profile for captured image.',
                    'recognition_algorithm' => $this->algorithmName(),
                ]),
            ]);

            return $this->emptyResult('Could not process captured image.');
        }

        $scoresByProduct = [];

        foreach ($this->candidateResources($store) as $resource) {
            $resourceProfile = $this->fingerprintForResource($resource);
            if (!$resourceProfile) {
                continue;
            }

            $scoreSet = $this->scoreProfiles($captureProfile, $resourceProfile);
            $productId = (int) $resource->id_product;

            if (!isset($scoresByProduct[$productId]) || $scoreSet['final_score'] > $scoresByProduct[$productId]['score']) {
                $scoresByProduct[$productId] = [
                    'product' => $resource->product,
                    'resource' => $resource,
                    'score' => $scoreSet['final_score'],
                    'scores' => $scoreSet,
                ];
            }
        }

        usort($scoresByProduct, fn ($a, $b) => $b['score'] <=> $a['score']);

        $debugTop = (int) config('webcatalogue.recognition.debug_top', 20);
        $storeDebug = (bool) config('webcatalogue.recognition.store_debug_matches', true);
        $maxStored = max($limit, $debugTop);
        $topScores = array_slice($scoresByProduct, 0, $maxStored);

        VisualRecognitionMatch::where('id_session', $session->id)
            ->whereIn('match_provider', [
                'internal_average_hash',
                'internal_phash',
                'internal_composite',
                'internal_composite_v2_26',
            ])
            ->delete();

        $suggestions = [];
        $debugMatches = [];
        $rank = 1;

        foreach ($topScores as $candidate) {
            if (!$candidate['product']) {
                continue;
            }

            $score = round((float) $candidate['score'], 4);
            $isVisibleCandidate = $rank <= $limit;
            $shouldPersist = $isVisibleCandidate || $storeDebug;
            $match = null;

            if ($shouldPersist) {
                $match = VisualRecognitionMatch::create([
                    'id_session' => $session->id,
                    'id_product' => $candidate['product']->id,
                    'match_provider' => 'internal_composite_v2_26',
                    'score' => $score,
                    'rank' => $rank,
                    'status' => 'suggested',
                    'metadata' => [
                        'resource_id' => $candidate['resource']->id,
                        'resource_type' => $candidate['resource']->resource_type,
                        'resource_path' => $candidate['resource']->file_path,
                        'algorithm' => $this->algorithmName(),
                        'scores' => $candidate['scores'],
                        'weights' => $this->normalisedWeights(),
                        'preprocess' => [
                            'object_crop_enabled' => (bool) config('webcatalogue.recognition.object_crop_enabled', true),
                            'object_crop_threshold' => (int) config('webcatalogue.recognition.object_crop_threshold', 28),
                            'center_crop_ratio' => $this->cropRatio(),
                            'profile_size' => 96,
                            'phash_size' => 32,
                            'edge_size' => 32,
                            'color_bins' => 4,
                        ],
                    ],
                ]);
            }

            $item = [
                'match_id' => $match?->id,
                'product_id' => $candidate['product']->id,
                'name' => strip_tags((string) $candidate['product']->name),
                'reference' => $candidate['product']->reference,
                'slug' => $candidate['product']->slug,
                'score' => round($candidate['score'], 2),
                'phash_score' => round($candidate['scores']['phash_score'] ?? 0, 2),
                'edge_score' => round($candidate['scores']['edge_score'] ?? 0, 2),
                'color_score' => round($candidate['scores']['color_score'] ?? 0, 2),
                'image_url' => $candidate['resource']->resolved_url,
                'product_url' => route('webcatalogue.front.product.show', [$store->slug, $candidate['product']->slug]),
            ];

            $debugMatches[] = $item;
            if ($isVisibleCandidate) {
                $suggestions[] = $item;
            }

            $rank++;
        }

        $autoThreshold = (float) config('webcatalogue.recognition.auto_match_threshold', 70);
        $suggestionThreshold = (float) config('webcatalogue.recognition.suggestion_threshold', 50);

        if (!empty($suggestions) && $suggestions[0]['score'] >= $autoThreshold) {
            $autoMatch = $suggestions[0];
            $session->update([
                'id_product' => $autoMatch['product_id'],
                'matched_score' => $autoMatch['score'],
                'matched_at' => now(),
                'status' => 'matched',
                'metadata' => array_merge($session->metadata ?: [], [
                    'recognition_algorithm' => $this->algorithmName(),
                    'auto_threshold' => $autoThreshold,
                    'suggestion_threshold' => $suggestionThreshold,
                    'best_debug_score' => $debugMatches[0]['score'] ?? null,
                    'best_debug_scores' => $debugMatches[0] ?? null,
                ]),
            ]);

            if (!empty($autoMatch['match_id'])) {
                VisualRecognitionMatch::where('id', $autoMatch['match_id'])->update(['status' => 'matched']);
            }

            $this->sendMatchedNotification($session, $autoMatch);

            return [
                'matched' => true,
                'auto_match' => $autoMatch,
                'suggestions' => $suggestions,
                'debug_matches' => $debugMatches,
                'message' => 'Product matched automatically.',
            ];
        }

        $suggestions = array_values(array_filter($suggestions, fn ($item) => $item['score'] >= $suggestionThreshold));

        $session->update([
            'status' => count($suggestions) ? 'suggestions_found' : 'no_match',
            'matched_score' => $debugMatches[0]['score'] ?? null,
            'metadata' => array_merge($session->metadata ?: [], [
                'recognition_algorithm' => $this->algorithmName(),
                'auto_threshold' => $autoThreshold,
                'suggestion_threshold' => $suggestionThreshold,
                'best_debug_score' => $debugMatches[0]['score'] ?? null,
                'best_debug_scores' => $debugMatches[0] ?? null,
                'debug_top_count' => count($debugMatches),
            ]),
        ]);

        return [
            'matched' => false,
            'auto_match' => null,
            'suggestions' => $suggestions,
            'debug_matches' => $debugMatches,
            'message' => count($suggestions) ? 'Possible product suggestions found.' : 'No product match found.',
        ];
    }

    private function emptyResult(string $message): array
    {
        return [
            'matched' => false,
            'auto_match' => null,
            'suggestions' => [],
            'debug_matches' => [],
            'message' => $message,
        ];
    }

    private function candidateResources(Store $store)
    {
        return Resource::query()
            ->with('product')
            ->where('id_store', $store->id)
            ->whereNotNull('id_product')
            ->whereNotNull('file_path')
            ->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])
            ->where(function ($query) {
                $query->whereNull('status')->orWhereNotIn('status', ['deleted', 'disabled', 'inactive']);
            })
            ->limit((int) config('webcatalogue.recognition.max_candidate_images', 800))
            ->get()
            ->filter(fn ($resource) => $resource->product instanceof Product);
    }

    private function fingerprintForResource(Resource $resource): ?array
    {
        if (!$resource->file_path || !Storage::disk('public')->exists($resource->file_path)) {
            return null;
        }

        $signature = $this->sourceSignature($resource->file_path);
        $algorithm = $this->algorithmName();

        $existing = ResourceFingerprint::where('id_resource', $resource->id)
            ->where('algorithm', $algorithm)
            ->first();

        if ($existing && $existing->source_signature === $signature && is_array($existing->vector_json)) {
            return $existing->vector_json;
        }

        $profile = $this->profileFromPublicPath($resource->file_path);
        if (!$profile) {
            return null;
        }

        ResourceFingerprint::updateOrCreate(
            ['id_resource' => $resource->id, 'algorithm' => $algorithm],
            [
                'id_store' => $resource->id_store,
                'id_product' => $resource->id_product,
                'hash_value' => $profile['phash'] ?? null,
                'vector_json' => $profile,
                'width' => $profile['source_width'] ?? null,
                'height' => $profile['source_height'] ?? null,
                'source_signature' => $signature,
                'metadata' => [
                    'resource_type' => $resource->resource_type,
                    'file_path' => $resource->file_path,
                    'generated_by' => 'InternalImageMatchService',
                ],
            ]
        );

        return $profile;
    }

    private function profileFromPublicPath(string $path): ?array
    {
        if (!extension_loaded('gd') || !Storage::disk('public')->exists($path)) {
            return null;
        }

        $binary = Storage::disk('public')->get($path);
        $image = @imagecreatefromstring($binary);
        if (!$image) {
            return null;
        }

        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $prepared = $this->prepareImage($image, 96);
        imagedestroy($image);

        if (!$prepared) {
            return null;
        }

        $phashImage = $this->resizeImage($prepared, 32, 32);
        $edgeImage = $this->resizeImage($prepared, 32, 32);

        $profile = [
            'algorithm' => $this->algorithmName(),
            'source_width' => $sourceWidth,
            'source_height' => $sourceHeight,
            'phash' => $phashImage ? $this->phashFromImage($phashImage) : null,
            'edge_hash' => $edgeImage ? $this->edgeHashFromImage($edgeImage) : null,
            'color_histogram' => $this->colorHistogramFromImage($prepared, 4),
        ];

        if ($phashImage) {
            imagedestroy($phashImage);
        }
        if ($edgeImage) {
            imagedestroy($edgeImage);
        }
        imagedestroy($prepared);

        if (!$profile['phash'] && !$profile['edge_hash'] && empty($profile['color_histogram'])) {
            return null;
        }

        return $profile;
    }

    private function prepareImage($image, int $size)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= 0 || $height <= 0) {
            return null;
        }

        [$srcX, $srcY, $cropWidth, $cropHeight] = $this->objectCropBox($image);

        $resized = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $image, 0, 0, $srcX, $srcY, $size, $size, $cropWidth, $cropHeight);

        if (defined('IMG_FILTER_CONTRAST')) {
            @imagefilter($resized, IMG_FILTER_CONTRAST, -5);
        }

        return $resized;
    }

    private function objectCropBox($image): array
    {
        $width = imagesx($image);
        $height = imagesy($image);

        if (!(bool) config('webcatalogue.recognition.object_crop_enabled', true)) {
            return $this->centerCropBox($width, $height);
        }

        $threshold = (int) config('webcatalogue.recognition.object_crop_threshold', 28);
        $border = $this->borderAverageColor($image, $width, $height);

        $minX = $width;
        $minY = $height;
        $maxX = 0;
        $maxY = 0;
        $found = false;
        $step = max(1, (int) floor(min($width, $height) / 180));

        for ($y = 0; $y < $height; $y += $step) {
            for ($x = 0; $x < $width; $x += $step) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $distance = sqrt((($r - $border[0]) ** 2) + (($g - $border[1]) ** 2) + (($b - $border[2]) ** 2));

                if ($distance > $threshold) {
                    $found = true;
                    $minX = min($minX, $x);
                    $minY = min($minY, $y);
                    $maxX = max($maxX, $x);
                    $maxY = max($maxY, $y);
                }
            }
        }

        if (!$found || ($maxX - $minX) < 12 || ($maxY - $minY) < 12) {
            return $this->centerCropBox($width, $height);
        }

        $paddingX = (int) round(($maxX - $minX) * 0.12);
        $paddingY = (int) round(($maxY - $minY) * 0.12);
        $minX = max(0, $minX - $paddingX);
        $minY = max(0, $minY - $paddingY);
        $maxX = min($width - 1, $maxX + $paddingX);
        $maxY = min($height - 1, $maxY + $paddingY);

        return [$minX, $minY, max(1, $maxX - $minX), max(1, $maxY - $minY)];
    }

    private function centerCropBox(int $width, int $height): array
    {
        $cropRatio = $this->cropRatio();
        $cropWidth = max(1, (int) round($width * $cropRatio));
        $cropHeight = max(1, (int) round($height * $cropRatio));
        $srcX = max(0, (int) floor(($width - $cropWidth) / 2));
        $srcY = max(0, (int) floor(($height - $cropHeight) / 2));

        return [$srcX, $srcY, $cropWidth, $cropHeight];
    }

    private function borderAverageColor($image, int $width, int $height): array
    {
        $samples = [];
        $stepX = max(1, (int) floor($width / 24));
        $stepY = max(1, (int) floor($height / 24));

        for ($x = 0; $x < $width; $x += $stepX) {
            $samples[] = imagecolorat($image, $x, 0);
            $samples[] = imagecolorat($image, $x, $height - 1);
        }
        for ($y = 0; $y < $height; $y += $stepY) {
            $samples[] = imagecolorat($image, 0, $y);
            $samples[] = imagecolorat($image, $width - 1, $y);
        }

        $r = $g = $b = 0;
        foreach ($samples as $rgb) {
            $r += ($rgb >> 16) & 0xFF;
            $g += ($rgb >> 8) & 0xFF;
            $b += $rgb & 0xFF;
        }

        $count = max(1, count($samples));
        return [(int) round($r / $count), (int) round($g / $count), (int) round($b / $count)];
    }

    private function resizeImage($image, int $width, int $height)
    {
        $resized = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($resized, 255, 255, 255);
        imagefill($resized, 0, 0, $white);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $resized;
    }

    private function phashFromImage($image): ?string
    {
        imagefilter($image, IMG_FILTER_GRAYSCALE);
        if (defined('IMG_FILTER_GAUSSIAN_BLUR')) {
            @imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
        }

        $matrix = [];
        for ($y = 0; $y < 32; $y++) {
            $row = [];
            for ($x = 0; $x < 32; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $row[] = (($r * 0.299) + ($g * 0.587) + ($b * 0.114));
            }
            $matrix[] = $row;
        }

        return $this->dctHash($matrix);
    }

    private function edgeHashFromImage($image): ?string
    {
        imagefilter($image, IMG_FILTER_GRAYSCALE);
        $magnitudes = [];

        for ($y = 1; $y < 31; $y++) {
            for ($x = 1; $x < 31; $x++) {
                $left = imagecolorat($image, $x - 1, $y) & 0xFF;
                $right = imagecolorat($image, $x + 1, $y) & 0xFF;
                $top = imagecolorat($image, $x, $y - 1) & 0xFF;
                $bottom = imagecolorat($image, $x, $y + 1) & 0xFF;
                $magnitudes[] = abs($right - $left) + abs($bottom - $top);
            }
        }

        if (!$magnitudes) {
            return null;
        }

        $sorted = $magnitudes;
        sort($sorted);
        $median = $sorted[(int) floor(count($sorted) / 2)] ?? 0;

        return implode('', array_map(fn ($value) => $value >= $median ? '1' : '0', $magnitudes));
    }

    private function colorHistogramFromImage($image, int $bins = 4): array
    {
        $histogram = array_fill(0, $bins * $bins * $bins, 0);
        $width = imagesx($image);
        $height = imagesy($image);
        $total = max(1, $width * $height);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $rb = min($bins - 1, (int) floor($r / (256 / $bins)));
                $gb = min($bins - 1, (int) floor($g / (256 / $bins)));
                $bb = min($bins - 1, (int) floor($b / (256 / $bins)));
                $index = ($rb * $bins * $bins) + ($gb * $bins) + $bb;
                $histogram[$index]++;
            }
        }

        return array_map(fn ($count) => $count / $total, $histogram);
    }

    private function dctHash(array $matrix): string
    {
        $size = 32;
        $lowSize = 8;
        $coefficients = [];

        for ($u = 0; $u < $lowSize; $u++) {
            for ($v = 0; $v < $lowSize; $v++) {
                $sum = 0.0;
                for ($x = 0; $x < $size; $x++) {
                    for ($y = 0; $y < $size; $y++) {
                        $sum += $matrix[$x][$y]
                            * cos(((2 * $x + 1) * $u * pi()) / (2 * $size))
                            * cos(((2 * $y + 1) * $v * pi()) / (2 * $size));
                    }
                }

                $alphaU = $u === 0 ? sqrt(1 / $size) : sqrt(2 / $size);
                $alphaV = $v === 0 ? sqrt(1 / $size) : sqrt(2 / $size);
                $coefficients[] = $alphaU * $alphaV * $sum;
            }
        }

        $withoutDc = array_slice($coefficients, 1);
        sort($withoutDc);
        $median = $withoutDc[(int) floor(count($withoutDc) / 2)] ?? 0;

        return implode('', array_map(fn ($value) => $value >= $median ? '1' : '0', array_slice($coefficients, 1)));
    }

    private function scoreProfiles(array $a, array $b): array
    {
        $phashScore = $this->scoreHashes((string) ($a['phash'] ?? ''), (string) ($b['phash'] ?? ''));
        $edgeScore = $this->scoreHashes((string) ($a['edge_hash'] ?? ''), (string) ($b['edge_hash'] ?? ''));
        $colorScore = $this->scoreHistograms($a['color_histogram'] ?? [], $b['color_histogram'] ?? []);
        $weights = $this->normalisedWeights();

        $final = ($phashScore * $weights['phash'])
            + ($edgeScore * $weights['edge'])
            + ($colorScore * $weights['color']);

        return [
            'final_score' => round($final, 4),
            'phash_score' => round($phashScore, 4),
            'edge_score' => round($edgeScore, 4),
            'color_score' => round($colorScore, 4),
        ];
    }

    private function scoreHashes(string $a, string $b): float
    {
        $length = min(strlen($a), strlen($b));
        if ($length === 0) {
            return 0.0;
        }

        $distance = 0;
        for ($i = 0; $i < $length; $i++) {
            if ($a[$i] !== $b[$i]) {
                $distance++;
            }
        }

        return max(0, (1 - ($distance / $length)) * 100);
    }

    private function scoreHistograms(array $a, array $b): float
    {
        $length = min(count($a), count($b));
        if ($length === 0) {
            return 0.0;
        }

        $intersection = 0.0;
        for ($i = 0; $i < $length; $i++) {
            $intersection += min((float) $a[$i], (float) $b[$i]);
        }

        return max(0, min(100, $intersection * 100));
    }

    private function normalisedWeights(): array
    {
        $weights = config('webcatalogue.recognition.composite_weights', []);
        $phash = max(0, (float) ($weights['phash'] ?? 0.45));
        $edge = max(0, (float) ($weights['edge'] ?? 0.35));
        $color = max(0, (float) ($weights['color'] ?? 0.20));
        $total = $phash + $edge + $color;

        if ($total <= 0) {
            return ['phash' => 0.45, 'edge' => 0.35, 'color' => 0.20];
        }

        return ['phash' => $phash / $total, 'edge' => $edge / $total, 'color' => $color / $total];
    }

    private function sourceSignature(string $path): ?string
    {
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return md5($path . '|' . Storage::disk('public')->size($path) . '|' . Storage::disk('public')->lastModified($path));
    }

    private function cropRatio(): float
    {
        $ratio = (float) config('webcatalogue.recognition.center_crop_ratio', 0.82);
        return min(1.0, max(0.55, $ratio));
    }

    private function algorithmName(): string
    {
        return 'composite_phash_color_edge_object_crop_v2_26';
    }

    private function sendMatchedNotification(VisualRecognitionSession $session, array $match): void
    {
        if (!function_exists('notifications_send')) {
            return;
        }

        notifications_send([
            'title' => 'Produto reconhecido',
            'message' => 'Um produto foi reconhecido automaticamente via Visual Recognition: ' . ($match['name'] ?? 'produto') . '.',
            'type' => 'success',
            'category' => 'webcatalogue',
            'priority' => 'low',
            'channels' => ['internal'],
            'users' => [1],
        ]);
    }
}
