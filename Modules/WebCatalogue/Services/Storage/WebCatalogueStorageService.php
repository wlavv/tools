<?php

namespace Modules\WebCatalogue\Services\Storage;

use Illuminate\Support\Facades\Storage;

class WebCatalogueStorageService
{
    public function ensureBaseStructure(): void
    {
        $disk = $this->disk();
        foreach ([
            $this->root(),
            $this->root() . '/stores',
            $this->root() . '/shared/placeholders',
            $this->root() . '/shared/templates',
            $this->root() . '/shared/viewer',
            $this->root() . '/temp',
        ] as $path) {
            $disk->makeDirectory($path);
        }
    }

    public function ensureStoreStructure(int $storeId): void
    {
        $disk = $this->disk();
        $base = $this->storePath($storeId);
        foreach ([
            $base . '/branding',
            $base . '/themes',
            $base . '/environments',
            $base . '/catalogues',
            $base . '/products',
            $base . '/imports',
            $base . '/exports',
        ] as $path) {
            $disk->makeDirectory($path);
        }
    }

    public function ensureProductStructure(int $storeId, int $productId): void
    {
        $disk = $this->disk();
        $base = $this->productPath($storeId, $productId);
        foreach ([
            $base . '/images',
            $base . '/documents',
            $base . '/videos',
            $base . '/audio',
            $base . '/models',
            $base . '/ar',
            $base . '/thumbnails',
            $base . '/temp',
        ] as $path) {
            $disk->makeDirectory($path);
        }
    }

    public function storePath(int $storeId): string
    {
        return $this->root() . '/stores/' . $storeId;
    }

    public function productPath(int $storeId, int $productId): string
    {
        return $this->storePath($storeId) . '/products/' . $productId;
    }

    protected function root(): string
    {
        return trim((string) config('webcatalogue.storage_root', 'webcatalogue'), '/');
    }

    protected function disk()
    {
        return Storage::disk((string) config('webcatalogue.storage_disk', 'public'));
    }
}
