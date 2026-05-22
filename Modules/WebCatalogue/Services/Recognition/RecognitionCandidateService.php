<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Support\Facades\Storage;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\ResourceFingerprint;
use Modules\WebCatalogue\Models\ResourceFingerprintProfile;
use Modules\WebCatalogue\Models\ResourceVisualMarker;
use Modules\WebCatalogue\Models\Store;

class RecognitionCandidateService
{
    public function retrieve($candidateResources, array $captureProfiles, array $captureMarkers, ?Store $store = null): array
    {
        $candidateCount = count($candidateResources);
        $preselected = [];
        $resourcesById = $this->resourcesById($candidateResources);
        $markerOnly = $this->markerScoringMode() === 'markers_only';
        $captureShortHashes = array_values(array_filter(array_map(
            fn ($captureProfile) => $this->shortProfileHash($captureProfile['profile'] ?? []),
            $captureProfiles
        )));
        $captureShortPrefixes = $this->shortHashPrefixes($captureShortHashes);
        $captureAspectBuckets = $this->aspectBucketsFromProfiles($captureProfiles);
        $retrievalSource = $markerOnly ? 'marker_hash_only' : 'resource_fingerprints_bucketed';
        $fingerprintsByResource = $markerOnly
            ? collect()
            : $this->existingFingerprintsForResources($resourcesById, $captureShortPrefixes, $captureAspectBuckets);

        if (!$markerOnly && $fingerprintsByResource->isEmpty() && (!empty($captureShortPrefixes) || !empty($captureAspectBuckets))) {
            $retrievalSource = 'resource_fingerprints_aspect_fallback';
            $fingerprintsByResource = $this->existingFingerprintsForResources($resourcesById, [], $captureAspectBuckets);
        }

        if (!$markerOnly && $fingerprintsByResource->isEmpty()) {
            $retrievalSource = 'resource_fingerprints_full_fallback';
            $fingerprintsByResource = $this->existingFingerprintsForResources($resourcesById);
        }

        foreach ($fingerprintsByResource as $resourceId => $fingerprint) {
            $resource = $resourcesById[(int) $resourceId] ?? null;
            if (!$resource) continue;

            $preselected[] = [
                'resource' => $resource,
                'fingerprint' => $fingerprint,
                'short_distance' => $this->bestShortHashDistance($captureShortHashes, $fingerprint->short_hash),
                'marker_hash_distance' => null,
                'candidate_sources' => ['short_hash'],
            ];
        }

        $fingerprintedCount = count($preselected);
        $preselected = $this->rankAndLimit($preselected, (int) config('webcatalogue.recognition.candidate_pipeline.hash_stage_limit', 36));
        $afterHashStage = count($preselected);
        $preselected = $this->mergeMarkerCandidates($preselected, $captureMarkers, $store);
        $preselected = $this->rankAndLimit($preselected, (int) config('webcatalogue.recognition.candidate_pipeline.marker_stage_limit', 36));
        $afterMarkerStage = count($preselected);
        $beforeVerificationPool = count($preselected);
        $preselected = $markerOnly
            ? $preselected
            : $this->mergeVerificationPool($preselected, $resourcesById, $captureProfiles, $captureShortHashes);
        $preselected = $this->rankAndLimit($preselected, (int) config('webcatalogue.recognition.candidate_pipeline.verification_stage_limit', 42));
        $afterVerificationStage = count($preselected);
        $verificationAdded = max(0, count($preselected) - $beforeVerificationPool);

        $shortHashLimit = (int) config('webcatalogue.recognition.short_hash_top_candidates', 50);
        $markerCandidateTop = (int) config('webcatalogue.recognition.visual_markers.candidate_top', 30);
        $markerCandidatePool = (int) config('webcatalogue.recognition.visual_markers.candidate_pool', 60);
        $verificationPoolSize = (int) config('webcatalogue.recognition.verification_pool.size', 120);
        $limit = $markerOnly && $markerCandidatePool <= 0
            ? count($preselected)
            : max($shortHashLimit, $markerCandidateTop, $verificationPoolSize);

        $finalLimit = min($limit, (int) config('webcatalogue.recognition.candidate_pipeline.final_stage_limit', 54));
        $limited = $this->rankAndLimit($preselected, $finalLimit);

        return [
            'candidates' => $limited,
            'stats' => [
                'candidate_resources' => $candidateCount,
                'fingerprinted_candidates' => $fingerprintedCount,
                'after_hash_stage' => $afterHashStage,
                'after_marker_stage' => $afterMarkerStage,
                'after_verification_stage' => $afterVerificationStage,
                'after_final_stage' => count($limited),
                'marker_augmented_candidates' => count($preselected),
                'verification_pool_added_candidates' => $verificationAdded,
                'verification_pool_enabled' => !$markerOnly && (bool) config('webcatalogue.recognition.verification_pool.enabled', true),
                'verification_pool_size' => $verificationPoolSize,
                'marker_scoring_mode' => $this->markerScoringMode(),
                'missing_fingerprint_candidates' => max(0, $candidateCount - $fingerprintedCount),
                'scored_candidates' => count($limited),
                'final_stage_limit' => $finalLimit,
                'short_hash_top_candidates' => $shortHashLimit,
                'marker_candidate_top' => $markerCandidateTop,
                'marker_candidate_pool' => $markerCandidatePool,
                'marker_exhaustive_candidates' => $markerOnly && $markerCandidatePool <= 0,
                'build_missing_fingerprints_during_match' => false,
                'retrieval_source' => $retrievalSource,
                'short_hash_prefixes' => $captureShortPrefixes,
                'aspect_ratio_buckets' => $captureAspectBuckets,
            ],
        ];
    }

    private function resourcesById($resources): array
    {
        $indexed = [];
        foreach ($resources as $resource) {
            if ($resource instanceof Resource) {
                $indexed[(int) $resource->id] = $resource;
            }
        }

        return $indexed;
    }

    private function rankAndLimit(array $candidates, int $limit): array
    {
        if (empty($candidates)) {
            return [];
        }

        usort($candidates, function ($a, $b) {
            $aRank = $this->candidateRankValue($a);
            $bRank = $this->candidateRankValue($b);

            if ($aRank === $bRank) {
                return ((float) ($b['verification_score'] ?? 0)) <=> ((float) ($a['verification_score'] ?? 0));
            }

            return $aRank <=> $bRank;
        });

        return array_slice($candidates, 0, max(1, $limit));
    }

    private function candidateRankValue(array $candidate): float
    {
        $short = $candidate['short_distance'] ?? null;
        $marker = $candidate['marker_hash_distance'] ?? null;
        $verification = $candidate['verification_distance'] ?? null;
        $verificationScore = $candidate['verification_score'] ?? null;

        if (is_numeric($verificationScore) && (float) $verificationScore >= 72) {
            $sources = $candidate['candidate_sources'] ?? [];
            $sourceBonus = (count(array_unique($sources)) - 1) * 2.0;
            $distance = max(0, 82 - (float) $verificationScore) * 0.35;

            if (is_numeric($short) && (float) $short <= 6) {
                $distance -= 1.0;
            }

            return max(0, $distance - $sourceBonus);
        }

        $best = min(
            is_numeric($short) ? (float) $short : 999,
            is_numeric($marker) ? ((float) $marker * 0.85) : 999,
            is_numeric($verification) ? ((float) $verification * 0.65) : 999
        );

        $sources = $candidate['candidate_sources'] ?? [];
        $sourceBonus = (count(array_unique($sources)) - 1) * 2.0;

        return max(0, $best - $sourceBonus);
    }

    private function existingFingerprintsForResources(array $resourcesById, array $shortHashPrefixes = [], array $aspectBuckets = [])
    {
        if (empty($resourcesById)) {
            return collect();
        }

        $fingerprints = ResourceFingerprint::query()
            ->whereIn('id_resource', array_keys($resourcesById))
            ->where('algorithm', $this->algorithmName())
            ->whereNotNull('short_hash')
            ->when(!empty($shortHashPrefixes), function ($query) use ($shortHashPrefixes) {
                $query->where(function ($query) use ($shortHashPrefixes) {
                    $query->whereIn('short_hash_prefix', $shortHashPrefixes)
                        ->orWhereNull('short_hash_prefix');
                });
            })
            ->when(!empty($aspectBuckets), function ($query) use ($aspectBuckets) {
                $query->where(function ($query) use ($aspectBuckets) {
                    $query->whereIn('aspect_ratio_bucket', $aspectBuckets)
                        ->orWhereNull('aspect_ratio_bucket');
                });
            })
            ->get()
            ->filter(function (ResourceFingerprint $fingerprint) use ($resourcesById): bool {
                $resource = $resourcesById[(int) $fingerprint->id_resource] ?? null;
                if (!$resource || !$resource->file_path || !Storage::disk('public')->exists($resource->file_path)) {
                    return false;
                }

                return $fingerprint->source_signature === $this->sourceSignature($resource->file_path);
            });

        return $fingerprints->keyBy('id_resource');
    }

    private function mergeMarkerCandidates(array $preselected, array $captureMarkers, ?Store $store): array
    {
        if (!(bool) config('webcatalogue.recognition.visual_markers.enabled', true) || empty($captureMarkers)) {
            return $preselected;
        }

        $captureMarkerHashes = array_values(array_filter(array_map(
            fn ($captureMarker) => $captureMarker['markers']['marker_hash'] ?? null,
            $captureMarkers
        )));

        if (empty($captureMarkerHashes)) {
            return $preselected;
        }

        $markerRows = ResourceVisualMarker::query()
            ->where('algorithm', (string) config('webcatalogue.recognition.visual_markers.algorithm', 'orb_v1'))
            ->whereNotNull('marker_hash')
            ->when($store, fn ($query) => $query->where('id_store', $store->id))
            ->get(['id_resource', 'marker_hash']);

        if ($markerRows->isEmpty()) {
            return $preselected;
        }

        $markerCandidates = [];
        foreach ($markerRows as $row) {
            $distance = $this->bestShortHashDistance($captureMarkerHashes, (string) $row->marker_hash);
            $resourceId = (int) $row->id_resource;

            if (!isset($markerCandidates[$resourceId]) || $distance < $markerCandidates[$resourceId]) {
                $markerCandidates[$resourceId] = $distance;
            }
        }

        asort($markerCandidates);
        $markerOnly = $this->markerScoringMode() === 'markers_only';
        $candidatePool = (int) config('webcatalogue.recognition.visual_markers.candidate_pool', 60);
        if ($candidatePool > 0) {
            $markerCandidates = array_slice($markerCandidates, 0, $candidatePool, true);
        } elseif (!$markerOnly) {
            $markerCandidates = [];
        }

        if (empty($markerCandidates)) {
            return $preselected;
        }

        $byResource = [];
        foreach ($preselected as $candidate) {
            $resourceId = (int) $candidate['resource']->id;
            $byResource[$resourceId] = $candidate;
        }

        $missingIds = array_values(array_diff(array_keys($markerCandidates), array_keys($byResource)));
        $missingResources = Resource::query()
            ->with('product.store')
            ->whereIn('id', $missingIds)
            ->whereNotNull('id_product')
            ->get()
            ->keyBy('id');
        $missingFingerprints = $this->existingFingerprintsForResources($missingResources->all());

        foreach ($markerCandidates as $resourceId => $distance) {
            if (isset($byResource[$resourceId])) {
                $byResource[$resourceId]['marker_hash_distance'] = $distance;
                $sources = $byResource[$resourceId]['candidate_sources'] ?? [];
                $sources[] = 'marker_hash';
                $byResource[$resourceId]['candidate_sources'] = array_values(array_unique($sources));
                continue;
            }

            $resource = $missingResources->get($resourceId);
            if (!$resource) {
                continue;
            }

            $fingerprint = $missingFingerprints->get($resource->id);
            if (!$fingerprint && !$markerOnly) {
                continue;
            }

            $byResource[$resourceId] = [
                'resource' => $resource,
                'fingerprint' => $fingerprint,
                'short_distance' => null,
                'marker_hash_distance' => $distance,
                'candidate_sources' => ['marker_hash'],
            ];
        }

        return array_values($byResource);
    }

    private function mergeVerificationPool(array $preselected, array $resourcesById, array $captureProfiles, array $captureShortHashes): array
    {
        if (!(bool) config('webcatalogue.recognition.verification_pool.enabled', true) || empty($resourcesById) || empty($captureProfiles)) {
            return $preselected;
        }

        $poolSize = max(0, (int) config('webcatalogue.recognition.verification_pool.size', 120));
        if ($poolSize <= 0) {
            return $preselected;
        }

        $byResource = [];
        foreach ($preselected as $candidate) {
            $resourceId = (int) $candidate['resource']->id;
            $byResource[$resourceId] = $candidate;
        }

        $fingerprints = $this->existingFingerprintsForResources($resourcesById);
        $profilesByFingerprint = ResourceFingerprintProfile::query()
            ->whereIn('id_fingerprint', $fingerprints->pluck('id')->all())
            ->get()
            ->keyBy('id_fingerprint');
        $ranked = [];

        foreach ($fingerprints as $resourceId => $fingerprint) {
            $resource = $resourcesById[(int) $resourceId] ?? null;
            if (!$resource || isset($byResource[(int) $resourceId])) {
                continue;
            }

            $profile = $profilesByFingerprint->get($fingerprint->id)?->profile_json;
            if (!is_array($profile) || empty($profile['variants'])) {
                $profile = is_array($fingerprint->vector_json) ? $fingerprint->vector_json : [];
            }

            $verificationScore = $this->bestRetrievalScore($captureProfiles, $profile);
            $shortDistance = $this->bestShortHashDistance($captureShortHashes, $fingerprint->short_hash);

            if ($verificationScore <= 0 && $shortDistance === null) {
                continue;
            }

            $ranked[] = [
                'resource' => $resource,
                'fingerprint' => $fingerprint,
                'short_distance' => $shortDistance,
                'marker_hash_distance' => null,
                'verification_score' => round($verificationScore, 4),
                'verification_distance' => round(max(0, 100 - $verificationScore), 4),
                'candidate_sources' => ['verification_pool'],
            ];
        }

        usort($ranked, function ($a, $b) {
            $scoreCompare = ((float) ($b['verification_score'] ?? 0)) <=> ((float) ($a['verification_score'] ?? 0));
            if ($scoreCompare !== 0) {
                return $scoreCompare;
            }

            return ((float) ($a['short_distance'] ?? 999)) <=> ((float) ($b['short_distance'] ?? 999));
        });

        foreach (array_slice($ranked, 0, $poolSize) as $candidate) {
            $byResource[(int) $candidate['resource']->id] = $candidate;
        }

        return array_values($byResource);
    }

    private function bestRetrievalScore(array $captureProfiles, array $resourceProfile): float
    {
        $best = 0.0;

        foreach ($captureProfiles as $captureProfile) {
            $best = max($best, $this->retrievalScore($captureProfile['profile'] ?? [], $resourceProfile));
        }

        return $best;
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

    private function shortProfileHash(array $profile, int $length = 28): ?string
    {
        $variant = $profile['variants']['object'] ?? $profile['variants']['center'] ?? $profile['variants']['full'] ?? null;
        if (!$variant) {
            return null;
        }

        $bits = '';
        $bits .= $this->sampleHashBits((string) ($variant['phash'] ?? ''), 12);
        $bits .= $this->sampleHashBits((string) ($variant['edge_hash'] ?? ''), 10);
        $bits .= $this->embeddingSignBits($variant['embedding'] ?? [], 6);

        return substr(str_pad($bits, $length, '0'), 0, $length);
    }

    private function shortHashPrefixes(array $shortHashes): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($hash) => is_string($hash) && $hash !== '' ? substr($hash, 0, 8) : null,
            $shortHashes
        ))));
    }

    private function aspectBucketsFromProfiles(array $captureProfiles): array
    {
        $buckets = [];
        foreach ($captureProfiles as $captureProfile) {
            $ratio = $captureProfile['profile']['object_aspect_ratio'] ?? null;
            if (!is_numeric($ratio) || (float) $ratio <= 0) {
                continue;
            }

            $bucket = (int) round(((float) $ratio) * 20);
            foreach ([-1, 0, 1] as $offset) {
                $buckets[] = max(1, $bucket + $offset);
            }
        }

        return array_values(array_unique($buckets));
    }

    private function sampleHashBits(string $hash, int $count): string
    {
        $length = strlen($hash);
        if ($length === 0 || $count <= 0) {
            return str_repeat('0', max(0, $count));
        }

        $bits = '';
        for ($i = 0; $i < $count; $i++) {
            $index = (int) floor(($i * max(1, $length - 1)) / max(1, $count - 1));
            $bits .= ($hash[$index] ?? '0') === '1' ? '1' : '0';
        }

        return $bits;
    }

    private function embeddingSignBits(array $embedding, int $count): string
    {
        if (!$embedding || $count <= 0) {
            return str_repeat('0', max(0, $count));
        }

        $bits = '';
        $length = count($embedding);
        for ($i = 0; $i < $count; $i++) {
            $index = (int) floor(($i * max(1, $length - 1)) / max(1, $count - 1));
            $bits .= ((float) ($embedding[$index] ?? 0)) >= 0 ? '1' : '0';
        }

        return $bits;
    }

    private function bestShortHashDistance(array $captureShortHashes, ?string $resourceShortHash): ?int
    {
        if (!$resourceShortHash || empty($captureShortHashes)) {
            return null;
        }

        $best = null;
        foreach ($captureShortHashes as $captureShortHash) {
            $distance = $this->shortHashDistance($captureShortHash, $resourceShortHash);
            if ($best === null || $distance < $best) {
                $best = $distance;
            }
        }

        return $best;
    }

    private function shortHashDistance(string $a, string $b): int
    {
        $length = min(strlen($a), strlen($b));
        if ($length === 0) {
            return 999;
        }

        $distance = abs(strlen($a) - strlen($b));
        for ($i = 0; $i < $length; $i++) {
            if ($a[$i] !== $b[$i]) {
                $distance++;
            }
        }

        return $distance;
    }

    private function sourceSignature(string $path): ?string
    {
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return md5($path . '|' . Storage::disk('public')->size($path) . '|' . Storage::disk('public')->lastModified($path));
    }

    private function markerScoringMode(): string
    {
        $mode = strtolower(trim((string) config('webcatalogue.recognition.visual_markers.scoring_mode', 'boost')));

        return in_array($mode, ['markers_only', 'marker_only', 'only'], true) ? 'markers_only' : 'boost';
    }

    private function algorithmName(): string
    {
        return 'opencv_short_hash_aux_profile_v3_9';
    }
}
