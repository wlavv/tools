<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Support\Facades\Storage;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\ResourceFingerprint;
use Modules\WebCatalogue\Models\ResourceVisualMarker;
use Modules\WebCatalogue\Models\Store;

class RecognitionCandidateService
{
    public function retrieve($candidateResources, array $captureProfiles, array $captureMarkers, ?Store $store = null): array
    {
        $candidateCount = count($candidateResources);
        $preselected = [];
        $resourcesById = $this->resourcesById($candidateResources);
        $captureShortHashes = array_values(array_filter(array_map(
            fn ($captureProfile) => $this->shortProfileHash($captureProfile['profile'] ?? []),
            $captureProfiles
        )));
        $captureShortPrefixes = $this->shortHashPrefixes($captureShortHashes);
        $captureAspectBuckets = $this->aspectBucketsFromProfiles($captureProfiles);
        $retrievalSource = 'resource_fingerprints_bucketed';
        $fingerprintsByResource = $this->existingFingerprintsForResources($resourcesById, $captureShortPrefixes, $captureAspectBuckets);

        if ($fingerprintsByResource->isEmpty() && (!empty($captureShortPrefixes) || !empty($captureAspectBuckets))) {
            $retrievalSource = 'resource_fingerprints_aspect_fallback';
            $fingerprintsByResource = $this->existingFingerprintsForResources($resourcesById, [], $captureAspectBuckets);
        }

        if ($fingerprintsByResource->isEmpty()) {
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
        $preselected = $this->mergeMarkerCandidates($preselected, $captureMarkers, $store);

        $shortHashLimit = (int) config('webcatalogue.recognition.short_hash_top_candidates', 50);
        $markerCandidateTop = (int) config('webcatalogue.recognition.visual_markers.candidate_top', 30);
        $limit = max($shortHashLimit, $markerCandidateTop);

        usort($preselected, function ($a, $b) {
            $aRank = min((float) ($a['short_distance'] ?? 999), (float) ($a['marker_hash_distance'] ?? 999));
            $bRank = min((float) ($b['short_distance'] ?? 999), (float) ($b['marker_hash_distance'] ?? 999));

            return $aRank <=> $bRank;
        });

        $limited = array_slice($preselected, 0, $limit);

        return [
            'candidates' => $limited,
            'stats' => [
                'candidate_resources' => $candidateCount,
                'fingerprinted_candidates' => $fingerprintedCount,
                'marker_augmented_candidates' => count($preselected),
                'missing_fingerprint_candidates' => max(0, $candidateCount - $fingerprintedCount),
                'scored_candidates' => count($limited),
                'short_hash_top_candidates' => $shortHashLimit,
                'marker_candidate_top' => $markerCandidateTop,
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
        $markerCandidates = array_slice(
            $markerCandidates,
            0,
            (int) config('webcatalogue.recognition.visual_markers.candidate_pool', 60),
            true
        );

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
            if (!$fingerprint) {
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

    private function algorithmName(): string
    {
        return 'opencv_short_hash_aux_profile_v3_9';
    }
}
