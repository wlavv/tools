<?php

namespace Modules\WebCatalogue\Services\Resources;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Services\Storage\WebCatalogueStorageService;

class WebCatalogueResourceUploadService
{
    public function __construct(protected WebCatalogueStorageService $storage) {}

    public function storeUploadedResource(UploadedFile $file, array $context): Resource
    {
        $storeId = (int) ($context['id_store'] ?? 0);
        $catalogueId = $context['id_catalogue'] ?? null;
        $productId = $context['id_product'] ?? null;
        $resourceType = (string) ($context['resource_type'] ?? 'download');
        $ownerType = $context['resource_owner_type'] ?? null;
        $ownerId = $context['resource_owner_id'] ?? null;
        $title = $context['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $description = $context['description'] ?? null;
        $isMain = (bool) ($context['is_main'] ?? false);
        $sortOrder = (int) ($context['sort_order'] ?? 0);
        $status = (string) ($context['status'] ?? 'active');
        $metadata = $context['metadata'] ?? null;
        $disk = (string) config('webcatalogue.storage_disk', 'public');

        $base = $this->basePath($storeId, $catalogueId ? (int) $catalogueId : null, $productId ? (int) $productId : null, $resourceType, $ownerType, $ownerId ? (int) $ownerId : null);
        Storage::disk($disk)->makeDirectory($base);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $safeType = preg_replace('/[^a-z0-9_\-]/i', '_', $resourceType);
        $filename = trim(($storeId ?: 'store') . '_' . ($productId ?: ($ownerId ?: 'resource')) . '_' . $safeType . '_' . date('Ymd_His') . '_' . substr(md5((string) microtime(true)), 0, 6) . '.' . $extension, '_');
        $path = $file->storeAs($base, $filename, $disk);

        return Resource::create([
            'id_store' => $storeId,
            'id_catalogue' => $catalogueId,
            'id_product' => $productId,
            'resource_owner_type' => $ownerType,
            'resource_owner_id' => $ownerId,
            'resource_type' => $resourceType,
            'title' => $title,
            'description' => $description,
            'source_type' => 'upload',
            'source_url' => null,
            'file_path' => $path,
            'public_url' => $disk === 'public' ? '/storage/' . ltrim($path, '/') : Storage::disk($disk)->url($path),
            'filename' => $filename,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'extension' => $extension,
            'is_main' => $isMain,
            'sort_order' => $sortOrder,
            'status' => $status,
            'metadata' => $metadata,
        ]);
    }

    protected function basePath(int $storeId, ?int $catalogueId, ?int $productId, string $resourceType, ?string $ownerType, ?int $ownerId): string
    {
        if ($storeId > 0 && $productId) {
            $this->storage->ensureProductStructure($storeId, $productId);
            return $this->storage->productPath($storeId, $productId) . '/' . $this->folderForType($resourceType);
        }
        if ($storeId > 0 && $catalogueId) {
            $this->storage->ensureStoreStructure($storeId);
            return $this->storage->storePath($storeId) . '/catalogues/' . $catalogueId . '/' . $this->folderForType($resourceType);
        }
        if ($storeId > 0 && $ownerType === 'store_theme' && $ownerId) {
            $this->storage->ensureStoreStructure($storeId);
            return $this->storage->storePath($storeId) . '/themes/' . $ownerId . '/' . $this->folderForType($resourceType);
        }
        if ($storeId > 0 && $ownerType === 'store_environment' && $ownerId) {
            $this->storage->ensureStoreStructure($storeId);
            return $this->storage->storePath($storeId) . '/environments/' . $ownerId . '/' . $this->folderForType($resourceType);
        }
        if ($storeId > 0) {
            $this->storage->ensureStoreStructure($storeId);
            return $this->storage->storePath($storeId) . '/branding/' . $this->folderForType($resourceType);
        }
        $this->storage->ensureBaseStructure();
        return 'webcatalogue/temp/' . $this->folderForType($resourceType);
    }

    protected function folderForType(string $resourceType): string
    {
        return match ($resourceType) {
            'image', 'gallery_image', 'cover', 'environment_background' => 'images',
            'thumbnail' => 'thumbnails',
            'video' => 'videos',
            'audio', 'ambient_audio', 'voiceover', 'sound_effect', 'music_track' => 'audio',
            'model_3d' => 'models',
            'ar_file' => 'ar',
            'vr_file', 'vr_scene' => 'vr',
            'skybox' => 'skyboxes',
            'floor_texture' => 'floors',
            'manual', 'datasheet', 'assembly_instructions', 'download' => 'documents',
            'logo', 'favicon' => 'logos',
            default => 'assets',
        };
    }
}
