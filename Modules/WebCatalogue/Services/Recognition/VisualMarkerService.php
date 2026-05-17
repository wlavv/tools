<?php

namespace Modules\WebCatalogue\Services\Recognition;

use Illuminate\Support\Facades\Storage;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\ResourceVisualMarker;
use Modules\WebCatalogue\Models\Store;

class VisualMarkerService
{
    public function __construct(private OpenCvRecognitionClient $client)
    {
    }

    public function rebuildStore(Store $store, bool $force = false): array
    {
        return $this->rebuildResources($this->imageResourcesQuery()->where('id_store', $store->id)->get(), $force);
    }

    public function rebuildProduct(Product $product, bool $force = false): array
    {
        return $this->rebuildResources($this->imageResourcesQuery()->where('id_product', $product->id)->get(), $force);
    }

    public function rebuildResource(Resource $resource, bool $force = false): string
    {
        if (!$this->canProcess($resource)) {
            return 'failed';
        }

        $signature = $this->sourceSignature($resource->file_path);
        $algorithm = $this->algorithmName();
        $this->removeDuplicateMarkers($resource, $algorithm);

        $existing = $this->existingMarker($resource, $algorithm);

        if (!$force && $existing && $existing->source_signature === $signature && (int) $existing->marker_count > 0) {
            return 'skipped';
        }

        $payload = $this->client->extractMarkers(
            $resource->file_path,
            (int) config('webcatalogue.recognition.visual_markers.max_markers', 250)
        );

        if (!$payload || empty($payload['descriptors'])) {
            return 'failed';
        }

        $data = [
            'id_store' => $resource->id_store,
            'id_product' => $resource->id_product,
            'marker_count' => (int) ($payload['marker_count'] ?? count($payload['descriptors'] ?? [])),
            'marker_hash' => $payload['marker_hash'] ?? null,
            'keypoints_json' => $payload['keypoints'] ?? [],
            'descriptors_json' => $payload['descriptors'] ?? [],
            'width' => $payload['width'] ?? null,
            'height' => $payload['height'] ?? null,
            'source_signature' => $signature,
            'metadata' => [
                'provider' => 'opencv_microservice',
                'descriptor_type' => $payload['descriptor_type'] ?? 'ORB',
                'generated_at' => now()->toIso8601String(),
                'force_rebuild' => $force,
            ],
        ];

        if ($existing) {
            $existing->update($data);
            return 'updated';
        }

        ResourceVisualMarker::create(array_merge($data, [
            'id_resource' => $resource->id,
            'algorithm' => $algorithm,
        ]));

        return 'created';
    }

    private function rebuildResources($resources, bool $force = false): array
    {
        $processed = 0;
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($resources as $resource) {
            $processed++;
            $status = $this->rebuildResource($resource, $force);

            if ($status === 'created') {
                $created++;
            } elseif ($status === 'updated') {
                $updated++;
            } elseif ($status === 'skipped') {
                $skipped++;
            } else {
                $failed++;
            }
        }

        return [
            'processed' => $processed,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'failed' => $failed,
            'algorithm' => $this->algorithmName(),
            'mode' => $force ? 'full_rebuild' : 'incremental',
        ];
    }

    private function existingMarker(Resource $resource, string $algorithm): ?ResourceVisualMarker
    {
        return ResourceVisualMarker::query()
            ->where('id_resource', $resource->id)
            ->where('algorithm', $algorithm)
            ->orderByDesc('id')
            ->first();
    }

    private function removeDuplicateMarkers(Resource $resource, string $algorithm): void
    {
        $markers = ResourceVisualMarker::query()
            ->where('id_resource', $resource->id)
            ->where('algorithm', $algorithm)
            ->orderByDesc('id')
            ->get(['id']);

        if ($markers->count() <= 1) {
            return;
        }

        ResourceVisualMarker::query()
            ->whereIn('id', $markers->slice(1)->pluck('id')->all())
            ->delete();
    }

    private function imageResourcesQuery()
    {
        return Resource::query()
            ->whereNotNull('id_product')
            ->whereNotNull('file_path')
            ->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])
            ->where(function ($query) {
                $query->whereNull('status')->orWhereNotIn('status', ['deleted', 'disabled', 'inactive']);
            });
    }

    private function canProcess(Resource $resource): bool
    {
        return (bool) config('webcatalogue.recognition.visual_markers.enabled', true)
            && $resource->file_path
            && Storage::disk('public')->exists($resource->file_path);
    }

    private function sourceSignature(string $path): ?string
    {
        if (!Storage::disk('public')->exists($path)) {
            return null;
        }

        return sha1($path . '|' . Storage::disk('public')->lastModified($path) . '|' . Storage::disk('public')->size($path));
    }

    private function algorithmName(): string
    {
        return (string) config('webcatalogue.recognition.visual_markers.algorithm', 'orb_v1');
    }
}
