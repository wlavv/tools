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

    public function rebuildStore(Store $store): array
    {
        return $this->rebuildResources($this->imageResourcesQuery()->where('id_store', $store->id)->get());
    }

    public function rebuildProduct(Product $product): array
    {
        return $this->rebuildResources($this->imageResourcesQuery()->where('id_product', $product->id)->get());
    }

    public function rebuildResource(Resource $resource): bool
    {
        if (!$this->canProcess($resource)) {
            return false;
        }

        $signature = $this->sourceSignature($resource->file_path);
        $algorithm = $this->algorithmName();
        $existing = ResourceVisualMarker::query()
            ->where('id_resource', $resource->id)
            ->where('algorithm', $algorithm)
            ->first();

        if ($existing && $existing->source_signature === $signature && (int) $existing->marker_count > 0) {
            return true;
        }

        $payload = $this->client->extractMarkers(
            $resource->file_path,
            (int) config('webcatalogue.recognition.visual_markers.max_markers', 250)
        );

        if (!$payload || empty($payload['descriptors'])) {
            return false;
        }

        ResourceVisualMarker::updateOrCreate(
            ['id_resource' => $resource->id, 'algorithm' => $algorithm],
            [
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
                ],
            ]
        );

        return true;
    }

    private function rebuildResources($resources): array
    {
        $processed = 0;
        $updated = 0;
        $failed = 0;

        foreach ($resources as $resource) {
            $processed++;
            if ($this->rebuildResource($resource)) {
                $updated++;
            } else {
                $failed++;
            }
        }

        return [
            'processed' => $processed,
            'updated' => $updated,
            'failed' => $failed,
            'algorithm' => $this->algorithmName(),
        ];
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
