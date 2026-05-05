<?php

namespace Modules\WebCatalogue\Services\ThreeD;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\ThreeDGenerationJob;
use RuntimeException;

class Meshy3DProvider
{
    protected string $baseUrl;
    protected ?string $apiKey;
    protected string $disk;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('webcatalogue.3d_generation.providers.meshy.base_url', 'https://api.meshy.ai'), '/');
        $this->apiKey = config('webcatalogue.3d_generation.providers.meshy.api_key');
        $this->disk = (string) config('webcatalogue.storage_disk', 'public');
    }

    public function submit(ThreeDGenerationJob $job): string
    {
        $this->assertConfigured();

        $imageUrls = $this->buildImageInputs($job);

        if (count($imageUrls) < 1) {
            throw new RuntimeException('Meshy requires at least one source image.');
        }

        $maxImages = (int) config('webcatalogue.3d_generation.providers.meshy.max_images', 4);
        $imageUrls = array_slice($imageUrls, 0, max(1, min(4, $maxImages)));

        $payload = [
            'image_urls' => $imageUrls,
            'should_texture' => (bool) config('webcatalogue.3d_generation.providers.meshy.should_texture', true),
            'enable_pbr' => (bool) config('webcatalogue.3d_generation.providers.meshy.enable_pbr', true),
            'target_formats' => config('webcatalogue.3d_generation.providers.meshy.target_formats', ['glb', 'usdz']),
            'ai_model' => config('webcatalogue.3d_generation.providers.meshy.ai_model', 'latest'),
        ];

        if (!empty($job->prompt)) {
            $payload['texture_prompt'] = (string) $job->prompt;
        }

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout((int) config('webcatalogue.3d_generation.providers.meshy.http_timeout', 120))
            ->post($this->baseUrl . '/openapi/v1/multi-image-to-3d', $payload);

        if (!$response->successful()) {
            throw new RuntimeException('Meshy submit failed: HTTP ' . $response->status() . ' · ' . $response->body());
        }

        $taskId = $response->json('result');

        if (!$taskId) {
            throw new RuntimeException('Meshy submit failed: missing task id in response.');
        }

        return (string) $taskId;
    }

    public function retrieve(string $taskId): array
    {
        $this->assertConfigured();

        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->timeout((int) config('webcatalogue.3d_generation.providers.meshy.http_timeout', 120))
            ->get($this->baseUrl . '/openapi/v1/multi-image-to-3d/' . urlencode($taskId));

        if (!$response->successful()) {
            throw new RuntimeException('Meshy status check failed: HTTP ' . $response->status() . ' · ' . $response->body());
        }

        return $response->json() ?: [];
    }

    public function downloadResults(ThreeDGenerationJob $job, array $task): array
    {
        $modelUrls = $task['model_urls'] ?? [];

        if (empty($modelUrls['glb'])) {
            throw new RuntimeException('Meshy task completed but no GLB output URL was returned.');
        }

        $resources = [];
        $resources['model'] = $this->downloadResource($job, $modelUrls['glb'], 'model_3d', 'Generated Meshy GLB · Job #' . $job->id, 'models', 'glb', 'model/gltf-binary', [
            'provider' => 'meshy',
            'provider_task_id' => $task['id'] ?? $job->provider_task_id,
            'meshy_task' => $this->compactTaskMetadata($task),
        ], true, 0);

        if (!empty($modelUrls['usdz'])) {
            $resources['ar'] = $this->downloadResource($job, $modelUrls['usdz'], 'ar_file', 'Generated Meshy USDZ · Job #' . $job->id, 'ar', 'usdz', 'model/vnd.usdz+zip', [
                'provider' => 'meshy',
                'provider_task_id' => $task['id'] ?? $job->provider_task_id,
                'ar_modes' => ['quick-look'],
            ], false, 1);
        } else {
            $resources['ar'] = $this->createLinkedResource($job, $resources['model'], 'ar_file', 'AR-ready GLB · Job #' . $job->id, [
                'provider' => 'meshy',
                'provider_task_id' => $task['id'] ?? $job->provider_task_id,
                'ar_modes' => ['webxr', 'scene-viewer'],
            ], 1);
        }

        if (!empty($task['thumbnail_url'])) {
            $resources['thumbnail'] = $this->downloadResource($job, $task['thumbnail_url'], 'thumbnail', 'Meshy preview thumbnail · Job #' . $job->id, 'thumbnails', 'png', 'image/png', [
                'provider' => 'meshy',
                'provider_task_id' => $task['id'] ?? $job->provider_task_id,
            ], false, 2);
        }

        $resources['vr'] = $this->createVrSceneResource($job, $resources['model']);

        return $resources;
    }

    protected function buildImageInputs(ThreeDGenerationJob $job): array
    {
        $ids = array_values(array_filter(array_map('intval', (array) $job->source_resource_ids)));
        $resources = Resource::query()->whereIn('id', $ids)->orderByRaw('FIELD(id,' . implode(',', $ids ?: [0]) . ')')->get();
        $inputs = [];

        foreach ($resources as $resource) {
            if (!$resource->is_image) {
                continue;
            }

            if (!empty($resource->file_path) && Storage::disk($this->disk)->exists($resource->file_path)) {
                $mime = $resource->mime_type ?: 'image/jpeg';
                $inputs[] = 'data:' . $mime . ';base64,' . base64_encode(Storage::disk($this->disk)->get($resource->file_path));
                continue;
            }

            $url = $resource->resolved_url;
            if ($url) {
                $inputs[] = $this->absoluteUrl($url);
            }
        }

        return array_values(array_unique($inputs));
    }

    protected function downloadResource(ThreeDGenerationJob $job, string $url, string $resourceType, string $title, string $folder, string $extension, string $mimeType, array $metadata = [], bool $isMain = false, int $sortOrder = 0): Resource
    {
        $response = Http::timeout((int) config('webcatalogue.3d_generation.providers.meshy.download_timeout', 300))->get($url);

        if (!$response->successful()) {
            throw new RuntimeException('Failed to download Meshy output [' . $resourceType . ']: HTTP ' . $response->status());
        }

        $storeId = (int) $job->id_store;
        $productId = (int) $job->id_product;
        $base = 'webcatalogue/stores/' . $storeId . '/products/' . $productId . '/' . $folder;
        Storage::disk($this->disk)->makeDirectory($base);

        $stamp = now()->format('Ymd_His') . '_' . Str::lower(Str::random(6));
        $filename = $storeId . '_' . $productId . '_' . $resourceType . '_' . $stamp . '.' . $extension;
        $path = $base . '/' . $filename;

        Storage::disk($this->disk)->put($path, $response->body());

        return Resource::create([
            'id_store' => $storeId,
            'id_product' => $productId,
            'resource_owner_type' => '3d_generation_job',
            'resource_owner_id' => $job->id,
            'resource_type' => $resourceType,
            'title' => $title,
            'description' => 'Generated by Meshy from WebCatalogue 3D Studio source images.',
            'source_type' => 'generated',
            'source_url' => $url,
            'file_path' => $path,
            'public_url' => Storage::disk($this->disk)->url($path),
            'filename' => $filename,
            'mime_type' => $mimeType,
            'file_size' => Storage::disk($this->disk)->size($path),
            'extension' => $extension,
            'is_main' => $isMain,
            'sort_order' => $sortOrder,
            'status' => 'active',
            'metadata' => $metadata,
        ]);
    }

    protected function createLinkedResource(ThreeDGenerationJob $job, Resource $source, string $resourceType, string $title, array $metadata = [], int $sortOrder = 0): Resource
    {
        return Resource::create([
            'id_store' => $job->id_store,
            'id_product' => $job->id_product,
            'resource_owner_type' => '3d_generation_job',
            'resource_owner_id' => $job->id,
            'resource_type' => $resourceType,
            'title' => $title,
            'description' => 'Linked immersive resource based on the generated GLB.',
            'source_type' => 'generated',
            'source_url' => $source->source_url,
            'file_path' => $source->file_path,
            'public_url' => $source->public_url,
            'filename' => $source->filename,
            'mime_type' => $source->mime_type,
            'file_size' => $source->file_size,
            'extension' => $source->extension,
            'is_main' => false,
            'sort_order' => $sortOrder,
            'status' => 'active',
            'metadata' => $metadata,
        ]);
    }

    protected function createVrSceneResource(ThreeDGenerationJob $job, Resource $model): Resource
    {
        $storeId = (int) $job->id_store;
        $productId = (int) $job->id_product;
        $base = 'webcatalogue/stores/' . $storeId . '/products/' . $productId . '/vr';
        Storage::disk($this->disk)->makeDirectory($base);

        $stamp = now()->format('Ymd_His') . '_' . Str::lower(Str::random(6));
        $filename = $storeId . '_' . $productId . '_vr_scene_' . $stamp . '.json';
        $path = $base . '/' . $filename;

        Storage::disk($this->disk)->put($path, json_encode([
            'type' => 'webcatalogue_vr_scene',
            'provider' => 'meshy',
            'job_id' => $job->id,
            'product_id' => $productId,
            'model_resource_id' => $model->id,
            'model_url' => $model->resolved_url,
            'environment' => 'default_showroom',
            'created_at' => now()->toDateTimeString(),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return Resource::create([
            'id_store' => $storeId,
            'id_product' => $productId,
            'resource_owner_type' => '3d_generation_job',
            'resource_owner_id' => $job->id,
            'resource_type' => 'vr_scene',
            'title' => 'VR scene config · Job #' . $job->id,
            'description' => 'Generated VR scene configuration pointing to the Meshy GLB.',
            'source_type' => 'generated',
            'file_path' => $path,
            'public_url' => Storage::disk($this->disk)->url($path),
            'filename' => $filename,
            'mime_type' => 'application/json',
            'file_size' => Storage::disk($this->disk)->size($path),
            'extension' => 'json',
            'is_main' => false,
            'sort_order' => 3,
            'status' => 'active',
            'metadata' => [
                'provider' => 'meshy',
                'job_id' => $job->id,
                'viewer' => 'webxr_threejs',
            ],
        ]);
    }

    protected function compactTaskMetadata(array $task): array
    {
        return [
            'id' => $task['id'] ?? null,
            'status' => $task['status'] ?? null,
            'progress' => $task['progress'] ?? null,
            'consumed_credits' => $task['consumed_credits'] ?? null,
            'created_at' => $task['created_at'] ?? null,
            'started_at' => $task['started_at'] ?? null,
            'finished_at' => $task['finished_at'] ?? null,
        ];
    }

    protected function absoluteUrl(string $url): string
    {
        if (Str::startsWith($url, ['http://', 'https://', 'data:'])) {
            return $url;
        }

        return url($url);
    }

    protected function assertConfigured(): void
    {
        if (empty($this->apiKey)) {
            throw new RuntimeException('Meshy API key missing. Set WEBCATALOGUE_MESHY_API_KEY in .env or switch WEBCATALOGUE_3D_GENERATION_MODE=mock.');
        }
    }
}
