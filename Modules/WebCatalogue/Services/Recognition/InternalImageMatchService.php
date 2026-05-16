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
        return $this->matchAgainstResources($session, $this->candidateResources($store), $store, $limit);
    }

    public function matchGlobalSession(VisualRecognitionSession $session, int $limit = 5): array
    {
        return $this->matchAgainstResources($session, $this->globalCandidateResources(), null, $limit);
    }

    private function matchAgainstResources(VisualRecognitionSession $session, $candidateResources, ?Store $store, int $limit = 5): array
    {
        $captures = $session->captures()
            ->where('capture_type', 'object_photo')
            ->latest()
            ->limit((int) config('webcatalogue.recognition.multi_frame_count', 3))
            ->get();

        if ($captures->isEmpty()) {
            $session->update(['status' => 'capture_missing']);
            return $this->emptyResult('No capture image available for matching.');
        }

        $captureProfiles = [];
        foreach ($captures as $capture) {
            if (!$capture->file_path) {
                continue;
            }

            $profilePath = $this->normalisedCaptureProfilePath($capture);
            $profile = $this->profileFromPublicPath($profilePath);
            if ($profile === null) {
                continue;
            }

            $this->persistCaptureAnalysis($capture, $profile, $profilePath);
            $captureProfiles[] = [
                'capture' => $capture,
                'profile' => $profile,
            ];
        }

        if (empty($captureProfiles)) {
            $session->update([
                'status' => 'match_failed',
                'metadata' => array_merge($session->metadata ?: [], [
                    'match_error' => 'Could not create image profile for captured images.',
                    'recognition_algorithm' => $this->algorithmName(),
                ]),
            ]);

            return $this->emptyResult('Could not process captured images.');
        }

        $preselected = [];

        foreach ($candidateResources as $resource) {
            $resourceProfile = $this->fingerprintForResource($resource);
            if (!$resourceProfile) {
                continue;
            }

            $bestRetrieval = 0.0;
            foreach ($captureProfiles as $captureProfile) {
                $bestRetrieval = max($bestRetrieval, $this->retrievalScore($captureProfile['profile'], $resourceProfile));
            }

            $preselected[] = [
                'resource' => $resource,
                'profile' => $resourceProfile,
                'retrieval_score' => $bestRetrieval,
            ];
        }

        usort($preselected, fn ($a, $b) => $b['retrieval_score'] <=> $a['retrieval_score']);
        $preselected = array_slice($preselected, 0, (int) config('webcatalogue.recognition.max_scored_candidates', 160));

        $scoresByProduct = [];

        foreach ($preselected as $candidateResource) {
            $resource = $candidateResource['resource'];
            $scoreSet = null;
            $scoreCapture = null;

            foreach ($captureProfiles as $captureProfile) {
                $candidateScore = $this->scoreProfiles($captureProfile['profile'], $candidateResource['profile']);
                $candidateScore['retrieval_score'] = round((float) $this->retrievalScore($captureProfile['profile'], $candidateResource['profile']), 4);

                if ($scoreSet === null || $candidateScore['final_score'] > $scoreSet['final_score']) {
                    $scoreSet = $candidateScore;
                    $scoreCapture = $captureProfile['capture'];
                }
            }

            if ($scoreSet === null) {
                continue;
            }

            $scoreSet['capture_id'] = $scoreCapture?->id;
            $scoreSet['multi_frame_count'] = count($captureProfiles);
            $scoreSet = $this->applyCaptureScoreBoost($scoreSet, $scoreCapture);
            $productId = (int) $resource->id_product;

            if (!isset($scoresByProduct[$productId]) || $scoreSet['final_score'] > $scoresByProduct[$productId]['score']) {
                $scoresByProduct[$productId] = [
                    'product' => $resource->product,
                    'resource' => $resource,
                    'capture' => $scoreCapture,
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
                'internal_composite_v2_27',
                'structured_region_embedding_phash_color_edge_v3_1',
                'structured_region_embedding_phash_color_edge_v3_2',
                'structured_region_embedding_phash_color_edge_v3_3',
                $this->algorithmName(),
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
                    'match_provider' => $this->algorithmName(),
                    'score' => $score,
                    'rank' => $rank,
                    'status' => 'suggested',
                    'metadata' => [
                        'resource_id' => $candidate['resource']->id,
                        'resource_type' => $candidate['resource']->resource_type,
                        'resource_path' => $candidate['resource']->file_path,
                        'capture_id' => $candidate['capture']?->id,
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
                            'variants' => ['object', 'center', 'full'],
                            'structured_regions' => ['name', 'art', 'text', 'footer'],
                            'candidate_resources' => count($candidateResources),
                            'scored_candidates' => count($preselected),
                            'capture_frames' => count($captureProfiles),
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
                'embedding_score' => round($candidate['scores']['embedding_score'] ?? 0, 2),
                'retrieval_score' => round($candidate['scores']['retrieval_score'] ?? 0, 2),
                'region_score' => round($candidate['scores']['region_score'] ?? 0, 2),
                'scoring_mode' => $candidate['scores']['scoring_mode'] ?? 'global',
                'image_url' => $candidate['resource']->resolved_url,
                'store_id' => $candidate['product']->id_store,
                'store_name' => $candidate['product']->store?->name,
                'store_slug' => $candidate['product']->store?->slug,
                'product_url' => $candidate['product']->store
                    ? route('webcatalogue.front.product.show', [$candidate['product']->store->slug, $candidate['product']->slug])
                    : null,
            ];

            $debugMatches[] = $item;
            if ($isVisibleCandidate) {
                $suggestions[] = $item;
            }

            $rank++;
        }

        $autoThreshold = (float) config('webcatalogue.recognition.auto_match_threshold', 70);
        $suggestionThreshold = (float) config('webcatalogue.recognition.suggestion_threshold', 50);
        $autoMargin = (float) config('webcatalogue.recognition.auto_match_min_margin', 5);
        $bestScore = (float) ($suggestions[0]['score'] ?? 0);
        $secondScore = (float) ($suggestions[1]['score'] ?? 0);
        $hasSafeMargin = count($suggestions) < 2 || (($bestScore - $secondScore) >= $autoMargin);

        if (!empty($suggestions) && $suggestions[0]['score'] >= $autoThreshold && $hasSafeMargin) {
            $autoMatch = $suggestions[0];
            $session->update([
                'id_product' => $autoMatch['product_id'],
                'matched_score' => $autoMatch['score'],
                'matched_at' => now(),
                'status' => 'matched',
                'metadata' => array_merge($session->metadata ?: [], [
                    'recognition_algorithm' => $this->algorithmName(),
                    'auto_threshold' => $autoThreshold,
                    'auto_min_margin' => $autoMargin,
                    'auto_margin' => round($bestScore - $secondScore, 2),
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
                'auto_min_margin' => $autoMargin,
                'auto_margin' => count($debugMatches) > 1 ? round(((float) $debugMatches[0]['score']) - ((float) $debugMatches[1]['score']), 2) : null,
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

    public function rebuildStoreDataset(Store $store): array
    {
        $processed = 0;
        $created = 0;
        $failed = 0;

        foreach ($this->candidateResources($store) as $resource) {
            $processed++;
            $before = ResourceFingerprint::where('id_resource', $resource->id)
                ->where('algorithm', $this->algorithmName())
                ->exists();

            $profile = $this->fingerprintForResource($resource);

            if (!$profile) {
                $failed++;
                continue;
            }

            if (!$before) {
                $created++;
            }
        }

        return [
            'processed' => $processed,
            'created' => $created,
            'updated' => max(0, $processed - $created - $failed),
            'failed' => $failed,
            'algorithm' => $this->algorithmName(),
        ];
    }

    public function rebuildProductDataset(Product $product): array
    {
        $processed = 0;
        $created = 0;
        $failed = 0;

        $resources = Resource::query()
            ->where('id_product', $product->id)
            ->whereNotNull('file_path')
            ->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])
            ->where(function ($query) {
                $query->whereNull('status')->orWhereNotIn('status', ['deleted', 'disabled', 'inactive']);
            })
            ->get();

        foreach ($resources as $resource) {
            $processed++;
            $before = ResourceFingerprint::where('id_resource', $resource->id)
                ->where('algorithm', $this->algorithmName())
                ->exists();

            $profile = $this->fingerprintForResource($resource);

            if (!$profile) {
                $failed++;
                continue;
            }

            if (!$before) {
                $created++;
            }
        }

        return [
            'processed' => $processed,
            'created' => $created,
            'updated' => max(0, $processed - $created - $failed),
            'failed' => $failed,
            'algorithm' => $this->algorithmName(),
        ];
    }

    public function compareSessionWithProduct(VisualRecognitionSession $session, Product $product): array
    {
        $capture = $session->captures()
            ->where('capture_type', 'object_photo')
            ->latest()
            ->first();

        if (!$capture || !$capture->file_path) {
            return ['ok' => false, 'message' => 'No capture image available for comparison.'];
        }

        $profilePath = $this->normalisedCaptureProfilePath($capture);
        $captureProfile = $this->profileFromPublicPath($profilePath);
        if (!$captureProfile) {
            return ['ok' => false, 'message' => 'Could not process captured image.'];
        }
        $this->persistCaptureAnalysis($capture, $captureProfile, $profilePath);

        $resources = Resource::query()
            ->where('id_product', $product->id)
            ->whereNotNull('file_path')
            ->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])
            ->where(function ($query) {
                $query->whereNull('status')->orWhereNotIn('status', ['deleted', 'disabled', 'inactive']);
            })
            ->get();

        $best = null;

        foreach ($resources as $resource) {
            $resourceProfile = $this->fingerprintForResource($resource);
            if (!$resourceProfile) {
                continue;
            }

            $scores = $this->scoreProfiles($captureProfile, $resourceProfile);
            $scores['retrieval_score'] = $this->retrievalScore($captureProfile, $resourceProfile);

            if ($best === null || $scores['final_score'] > $best['score']) {
                $best = [
                    'resource' => $resource,
                    'score' => $scores['final_score'],
                    'scores' => $scores,
                ];
            }
        }

        if (!$best) {
            return ['ok' => false, 'message' => 'No comparable product image found.'];
        }

        return [
            'ok' => true,
            'product_id' => $product->id,
            'product_reference' => $product->reference,
            'product_name' => strip_tags((string) $product->name),
            'resource_id' => $best['resource']->id,
            'resource_url' => $best['resource']->resolved_url,
            'score' => round((float) $best['score'], 4),
            'scores' => $best['scores'],
            'provider' => $this->algorithmName(),
            'message' => 'Forced comparison completed.',
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
            ->with('product.store')
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

    private function globalCandidateResources()
    {
        return Resource::query()
            ->with('product.store')
            ->whereNotNull('id_product')
            ->whereNotNull('file_path')
            ->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])
            ->where(function ($query) {
                $query->whereNull('status')->orWhereNotIn('status', ['deleted', 'disabled', 'inactive']);
            })
            ->whereHas('product.store', fn ($query) => $query->where('status', 'active'))
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
        $objectBox = $this->objectCropBox($image);
        $variants = [
            'object' => $this->profileVariantFromBox($image, $objectBox, 96),
            'center' => $this->profileVariantFromBox($image, $this->centerCropBox($sourceWidth, $sourceHeight), 96),
            'full' => $this->profileVariantFromBox($image, [0, 0, $sourceWidth, $sourceHeight], 96),
        ];
        $regions = $this->structuredRegionProfiles($image, $objectBox, 96);
        $variants = array_filter($variants);
        imagedestroy($image);

        if (empty($variants)) {
            return null;
        }

        $primary = $variants['object'] ?? $variants['center'] ?? reset($variants);

        $profile = [
            'algorithm' => $this->algorithmName(),
            'source_width' => $sourceWidth,
            'source_height' => $sourceHeight,
            'object_box' => $objectBox,
            'object_aspect_ratio' => $this->boxAspectRatio($objectBox),
            'structured_regions' => $regions,
            'phash' => $primary['phash'] ?? null,
            'edge_hash' => $primary['edge_hash'] ?? null,
            'color_histogram' => $primary['color_histogram'] ?? [],
            'variants' => $variants,
        ];

        if (!$profile['phash'] && !$profile['edge_hash'] && empty($profile['color_histogram'])) {
            return null;
        }

        return $profile;
    }

    private function normalisedCaptureProfilePath(VisualRecognitionCapture $capture): string
    {
        $metadata = $capture->metadata ?: [];
        $existing = $metadata['opencv_analysis']['normalized_path'] ?? null;
        if ($existing && Storage::disk('public')->exists($existing)) {
            return $existing;
        }

        $result = app(OpenCvRecognitionClient::class)->normalizeCapture($capture);
        if (!empty($result['normalized_path']) && Storage::disk('public')->exists($result['normalized_path'])) {
            return $result['normalized_path'];
        }

        return $capture->file_path;
    }

    private function persistCaptureAnalysis(VisualRecognitionCapture $capture, array $profile, ?string $sourcePath = null): void
    {
        $sourcePath = $sourcePath ?: $capture->file_path;

        if (!$sourcePath || !Storage::disk('public')->exists($sourcePath)) {
            return;
        }

        $box = $profile['object_box'] ?? null;
        if (!is_array($box) || count($box) < 4) {
            return;
        }

        $metadata = $capture->metadata ?: [];
        if (
            !empty($metadata['detected_object_crop_path'])
            && Storage::disk('public')->exists($metadata['detected_object_crop_path'])
            && (($metadata['recognition_analysis']['algorithm'] ?? null) === $this->algorithmName())
            && (($metadata['recognition_analysis']['source_path'] ?? null) === $sourcePath)
        ) {
            return;
        }

        $binary = Storage::disk('public')->get($sourcePath);
        $image = @imagecreatefromstring($binary);
        if (!$image) {
            return;
        }

        [$x, $y, $width, $height] = array_map('intval', array_slice($box, 0, 4));
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $x = max(0, min($sourceWidth - 1, $x));
        $y = max(0, min($sourceHeight - 1, $y));
        $width = max(1, min($sourceWidth - $x, $width));
        $height = max(1, min($sourceHeight - $y, $height));

        $crop = imagecreatetruecolor($width, $height);
        imagecopyresampled($crop, $image, 0, 0, $x, $y, $width, $height, $width, $height);

        $directory = trim(dirname($capture->file_path), '/') . '/analysis';
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME) . '_detected_crop.jpg';
        $path = $directory . '/' . $filename;

        ob_start();
        imagejpeg($crop, null, 92);
        $jpeg = ob_get_clean();
        Storage::disk('public')->put($path, $jpeg);

        imagedestroy($crop);
        imagedestroy($image);

        $capture->update([
            'metadata' => array_replace_recursive($metadata, [
                'recognition_analysis' => [
                    'algorithm' => $this->algorithmName(),
                    'source_path' => $sourcePath,
                    'source_width' => $profile['source_width'] ?? $sourceWidth,
                    'source_height' => $profile['source_height'] ?? $sourceHeight,
                    'object_box' => [$x, $y, $width, $height],
                    'object_aspect_ratio' => $profile['object_aspect_ratio'] ?? null,
                    'structured_regions' => array_keys($profile['structured_regions'] ?? []),
                    'generated_at' => now()->toIso8601String(),
                ],
                'detected_object_crop_path' => $path,
                'detected_object_crop_url' => Storage::disk('public')->url($path),
            ]),
        ]);
    }

    private function profileVariantFromBox($image, array $box, int $size): ?array
    {
        $prepared = $this->prepareImageFromBox($image, $box, $size);

        if (!$prepared) {
            return null;
        }

        $phashImage = $this->resizeImage($prepared, 32, 32);
        $edgeImage = $this->resizeImage($prepared, 32, 32);

        $profile = [
            'phash' => $phashImage ? $this->phashFromImage($phashImage) : null,
            'edge_hash' => $edgeImage ? $this->edgeHashFromImage($edgeImage) : null,
            'color_histogram' => $this->colorHistogramFromImage($prepared, 4),
            'embedding' => $this->embeddingFromImage($prepared, 12),
        ];

        if ($phashImage) {
            imagedestroy($phashImage);
        }
        if ($edgeImage) {
            imagedestroy($edgeImage);
        }
        imagedestroy($prepared);

        return $profile;
    }

    private function structuredRegionProfiles($image, array $objectBox, int $size): array
    {
        if (!$this->looksLikeCard($objectBox)) {
            return [];
        }

        [$x, $y, $width, $height] = $objectBox;
        $regions = [
            'name' => [0.08, 0.055, 0.84, 0.105],
            'art' => [0.08, 0.18, 0.84, 0.325],
            'text' => [0.08, 0.555, 0.84, 0.285],
            'footer' => [0.08, 0.855, 0.84, 0.085],
        ];

        $profiles = [];
        foreach ($regions as $name => [$rx, $ry, $rw, $rh]) {
            $box = [
                (int) round($x + ($width * $rx)),
                (int) round($y + ($height * $ry)),
                max(1, (int) round($width * $rw)),
                max(1, (int) round($height * $rh)),
            ];

            $profile = $this->profileVariantFromBox($image, $box, $size);
            if ($profile) {
                $profiles[$name] = $profile;
            }
        }

        return $profiles;
    }

    private function looksLikeCard(array $box): bool
    {
        $ratio = $this->boxAspectRatio($box);

        return $ratio >= 1.18 && $ratio <= 1.72;
    }

    private function boxAspectRatio(array $box): float
    {
        $width = max(1, (float) ($box[2] ?? 1));
        $height = max(1, (float) ($box[3] ?? 1));

        return round($height / $width, 4);
    }

    private function prepareImage($image, int $size)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        if ($width <= 0 || $height <= 0) {
            return null;
        }

        [$srcX, $srcY, $cropWidth, $cropHeight] = $this->objectCropBox($image);

        return $this->prepareImageFromBox($image, [$srcX, $srcY, $cropWidth, $cropHeight], $size);
    }

    private function prepareImageFromBox($image, array $box, int $size)
    {
        [$srcX, $srcY, $cropWidth, $cropHeight] = $box;
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

        $cardBox = $this->darkRectangularObjectBox($image, $width, $height);
        if ($cardBox !== null) {
            return $cardBox;
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

    private function darkRectangularObjectBox($image, int $width, int $height): ?array
    {
        if ($width < 80 || $height < 80) {
            return null;
        }

        $gridWidth = 120;
        $gridHeight = max(1, (int) round($height * ($gridWidth / $width)));
        $gridHeight = min(180, max(80, $gridHeight));
        $mask = [];

        for ($gy = 0; $gy < $gridHeight; $gy++) {
            $mask[$gy] = [];
            $yRatio = ($gy + 0.5) / $gridHeight;

            for ($gx = 0; $gx < $gridWidth; $gx++) {
                $xRatio = ($gx + 0.5) / $gridWidth;
                $x = min($width - 1, max(0, (int) round($xRatio * $width)));
                $y = min($height - 1, max(0, (int) round($yRatio * $height)));
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $luma = (0.299 * $r) + (0.587 * $g) + (0.114 * $b);
                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                $saturation = $max > 0 ? (($max - $min) / $max) : 0;

                $mask[$gy][$gx] = $luma < 62 || ($luma < 105 && $saturation > 0.18);
            }
        }

        $visited = array_fill(0, $gridHeight, array_fill(0, $gridWidth, false));
        $best = null;
        $edgeMarginX = max(1, (int) floor($gridWidth * 0.03));
        $edgeMarginY = max(1, (int) floor($gridHeight * 0.03));

        for ($gy = 0; $gy < $gridHeight; $gy++) {
            for ($gx = 0; $gx < $gridWidth; $gx++) {
                if ($visited[$gy][$gx] || !$mask[$gy][$gx]) {
                    continue;
                }

                $queue = [[$gx, $gy]];
                $visited[$gy][$gx] = true;
                $minX = $maxX = $gx;
                $minY = $maxY = $gy;
                $count = 0;
                $touchesEdge = false;

                while ($queue) {
                    [$x, $y] = array_pop($queue);
                    $count++;
                    $minX = min($minX, $x);
                    $maxX = max($maxX, $x);
                    $minY = min($minY, $y);
                    $maxY = max($maxY, $y);
                    $touchesEdge = $touchesEdge
                        || $x <= $edgeMarginX
                        || $y <= $edgeMarginY
                        || $x >= ($gridWidth - 1 - $edgeMarginX)
                        || $y >= ($gridHeight - 1 - $edgeMarginY);

                    foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
                        $nx = $x + $dx;
                        $ny = $y + $dy;
                        if ($nx < 0 || $ny < 0 || $nx >= $gridWidth || $ny >= $gridHeight) {
                            continue;
                        }
                        if ($visited[$ny][$nx] || !$mask[$ny][$nx]) {
                            continue;
                        }

                        $visited[$ny][$nx] = true;
                        $queue[] = [$nx, $ny];
                    }
                }

                if ($touchesEdge || $count < 35) {
                    continue;
                }

                $boxWidth = $maxX - $minX + 1;
                $boxHeight = $maxY - $minY + 1;
                $widthRatio = $boxWidth / $gridWidth;
                $heightRatio = $boxHeight / $gridHeight;
                $aspect = $boxHeight / max(1, $boxWidth);

                if ($aspect < 1.1 || $aspect > 1.9 || $widthRatio < 0.24 || $heightRatio < 0.30 || $widthRatio > 0.92 || $heightRatio > 0.96) {
                    continue;
                }

                $centerX = ($minX + $maxX) / 2;
                $centerY = ($minY + $maxY) / 2;
                $centerPenalty = (abs($centerX - ($gridWidth / 2)) / $gridWidth) + (abs($centerY - ($gridHeight / 2)) / $gridHeight);
                $score = ($boxWidth * $boxHeight) * (1 - min(0.65, $centerPenalty));

                if ($best === null || $score > $best['score']) {
                    $best = compact('minX', 'minY', 'maxX', 'maxY', 'score');
                }
            }
        }

        if ($best === null) {
            return null;
        }

        $x = (int) floor(($best['minX'] / $gridWidth) * $width);
        $y = (int) floor(($best['minY'] / $gridHeight) * $height);
        $boxWidth = (int) ceil((($best['maxX'] - $best['minX'] + 1) / $gridWidth) * $width);
        $boxHeight = (int) ceil((($best['maxY'] - $best['minY'] + 1) / $gridHeight) * $height);
        $paddingX = (int) round($boxWidth * 0.035);
        $paddingY = (int) round($boxHeight * 0.035);

        $x = max(0, $x - $paddingX);
        $y = max(0, $y - $paddingY);
        $right = min($width - 1, $x + $boxWidth + ($paddingX * 2));
        $bottom = min($height - 1, $y + $boxHeight + ($paddingY * 2));

        return [$x, $y, max(1, $right - $x), max(1, $bottom - $y)];
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

    private function embeddingFromImage($image, int $size = 12): array
    {
        $thumb = $this->resizeImage($image, $size, $size);
        if (!$thumb) {
            return [];
        }

        $values = [];
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                $rgb = imagecolorat($thumb, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $values[] = round(((($r * 0.299) + ($g * 0.587) + ($b * 0.114)) / 255), 6);
            }
        }

        imagedestroy($thumb);

        return $this->normaliseVector($values);
    }

    private function normaliseVector(array $values): array
    {
        if (!$values) {
            return [];
        }

        $mean = array_sum($values) / count($values);
        $centred = array_map(fn ($value) => (float) $value - $mean, $values);
        $norm = sqrt(array_sum(array_map(fn ($value) => $value * $value, $centred)));

        if ($norm <= 0) {
            return array_fill(0, count($values), 0.0);
        }

        return array_map(fn ($value) => round($value / $norm, 6), $centred);
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
        $aVariants = !empty($a['variants']) && is_array($a['variants']) ? $a['variants'] : ['primary' => $a];
        $bVariants = !empty($b['variants']) && is_array($b['variants']) ? $b['variants'] : ['primary' => $b];
        $best = null;

        foreach ($aVariants as $aName => $aVariant) {
            foreach ($bVariants as $bName => $bVariant) {
                $score = $this->scoreSingleProfiles($aVariant, $bVariant);
                $score['capture_variant'] = (string) $aName;
                $score['resource_variant'] = (string) $bName;

                if ($best === null || $score['final_score'] > $best['final_score']) {
                    $best = $score;
                }
            }
        }

        $best = $best ?: $this->scoreSingleProfiles($a, $b);
        $regionScore = $this->scoreStructuredRegions($a['structured_regions'] ?? [], $b['structured_regions'] ?? []);

        if ($regionScore) {
            $globalScore = (float) ($best['final_score'] ?? 0);
            $regionWeight = (float) config('webcatalogue.recognition.region_structured_weight', 0.55);
            $globalWeight = (float) config('webcatalogue.recognition.region_global_weight', 0.45);
            $totalWeight = max(0.0001, $regionWeight + $globalWeight);
            $finalScore = (($regionScore['region_score'] * $regionWeight) + ($globalScore * $globalWeight)) / $totalWeight;
            $best['global_score'] = round($globalScore, 4);
            $best['region_score'] = round($regionScore['region_score'], 4);
            $best['region_scores'] = $regionScore['regions'];
            $best['final_score'] = round($finalScore, 4);
            $best['scoring_mode'] = 'structured_regions';
        } else {
            $best['scoring_mode'] = 'global';
        }

        return $best;
    }

    private function scoreStructuredRegions(array $aRegions, array $bRegions): ?array
    {
        $weights = config('webcatalogue.recognition.region_weights', []);
        $weights = [
            'art' => max(0, (float) ($weights['art'] ?? 0.45)),
            'name' => max(0, (float) ($weights['name'] ?? 0.30)),
            'text' => max(0, (float) ($weights['text'] ?? 0.20)),
            'footer' => max(0, (float) ($weights['footer'] ?? 0.05)),
        ];
        $weightedScore = 0.0;
        $totalWeight = 0.0;
        $scores = [];

        foreach ($weights as $region => $weight) {
            if (empty($aRegions[$region]) || empty($bRegions[$region])) {
                continue;
            }

            $score = $this->scoreSingleProfiles($aRegions[$region], $bRegions[$region]);
            $scores[$region] = $score;
            $weightedScore += ((float) $score['final_score']) * $weight;
            $totalWeight += $weight;
        }

        if ($totalWeight <= 0) {
            return null;
        }

        return [
            'region_score' => $weightedScore / $totalWeight,
            'regions' => $scores,
        ];
    }

    private function applyCaptureScoreBoost(array $scoreSet, ?VisualRecognitionCapture $capture): array
    {
        if (!$capture || empty($capture->metadata['opencv_analysis']['ok'])) {
            return $scoreSet;
        }

        $boost = max(0, (float) config('webcatalogue.recognition.opencv.score_boost', 3));
        if ($boost <= 0) {
            return $scoreSet;
        }

        $before = (float) ($scoreSet['final_score'] ?? 0);
        $scoreSet['final_score_before_boost'] = round($before, 4);
        $scoreSet['opencv_boost'] = round($boost, 4);
        $scoreSet['final_score'] = round(min(100, $before + $boost), 4);

        return $scoreSet;
    }

    private function scoreSingleProfiles(array $a, array $b): array
    {
        $embeddingScore = $this->scoreEmbeddings($a['embedding'] ?? [], $b['embedding'] ?? []);
        $phashScore = $this->scoreHashes((string) ($a['phash'] ?? ''), (string) ($b['phash'] ?? ''));
        $edgeScore = $this->scoreHashes((string) ($a['edge_hash'] ?? ''), (string) ($b['edge_hash'] ?? ''));
        $colorScore = $this->scoreHistograms($a['color_histogram'] ?? [], $b['color_histogram'] ?? []);
        $weights = $this->normalisedWeights();

        $final = ($embeddingScore * $weights['embedding'])
            + ($phashScore * $weights['phash'])
            + ($edgeScore * $weights['edge'])
            + ($colorScore * $weights['color']);

        return [
            'final_score' => round($final, 4),
            'embedding_score' => round($embeddingScore, 4),
            'phash_score' => round($phashScore, 4),
            'edge_score' => round($edgeScore, 4),
            'color_score' => round($colorScore, 4),
        ];
    }

    private function retrievalScore(array $captureProfile, array $resourceProfile): float
    {
        $captureVariants = !empty($captureProfile['variants']) && is_array($captureProfile['variants'])
            ? $captureProfile['variants']
            : ['primary' => $captureProfile];
        $resourceVariants = !empty($resourceProfile['variants']) && is_array($resourceProfile['variants'])
            ? $resourceProfile['variants']
            : ['primary' => $resourceProfile];
        $best = 0.0;

        foreach ($captureVariants as $captureVariant) {
            foreach ($resourceVariants as $resourceVariant) {
                $embedding = $this->scoreEmbeddings($captureVariant['embedding'] ?? [], $resourceVariant['embedding'] ?? []);
                $color = $this->scoreHistograms($captureVariant['color_histogram'] ?? [], $resourceVariant['color_histogram'] ?? []);
                $best = max($best, ($embedding * 0.75) + ($color * 0.25));
            }
        }

        return round($best, 4);
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

    private function scoreEmbeddings(array $a, array $b): float
    {
        $length = min(count($a), count($b));
        if ($length === 0) {
            return 0.0;
        }

        $dot = 0.0;
        for ($i = 0; $i < $length; $i++) {
            $dot += ((float) $a[$i]) * ((float) $b[$i]);
        }

        return max(0, min(100, (($dot + 1) / 2) * 100));
    }

    private function normalisedWeights(): array
    {
        $weights = config('webcatalogue.recognition.composite_weights', []);
        $embedding = max(0, (float) ($weights['embedding'] ?? 0.35));
        $phash = max(0, (float) ($weights['phash'] ?? 0.30));
        $edge = max(0, (float) ($weights['edge'] ?? 0.20));
        $color = max(0, (float) ($weights['color'] ?? 0.15));
        $total = $embedding + $phash + $edge + $color;

        if ($total <= 0) {
            return ['embedding' => 0.35, 'phash' => 0.30, 'edge' => 0.20, 'color' => 0.15];
        }

        return ['embedding' => $embedding / $total, 'phash' => $phash / $total, 'edge' => $edge / $total, 'color' => $color / $total];
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
        return 'structured_region_embedding_phash_color_edge_v3_4';
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
