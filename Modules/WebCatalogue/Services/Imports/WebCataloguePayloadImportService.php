<?php

namespace Modules\WebCatalogue\Services\Imports;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\ProductPrice;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Store;

class WebCataloguePayloadImportService
{
    public function import(array $payload): array
    {
        $store = $this->upsertStore($payload['store'] ?? []);
        $catalogue = $this->upsertCatalogue($store, $payload['catalogue'] ?? []);

        $products = 0;
        $resources = 0;
        $prices = 0;
        $imagesDownloaded = 0;

        foreach (($payload['products'] ?? []) as $index => $productPayload) {
            $product = $this->upsertProduct($store, $productPayload);
            $this->attachToCatalogue($product, $catalogue, (int) ($productPayload['sort_order'] ?? $index));
            $products++;

            foreach (($productPayload['prices'] ?? []) as $pricePayload) {
                $this->upsertPrice($store, $product, $pricePayload);
                $prices++;
            }

            foreach (($productPayload['resources'] ?? []) as $resourcePayload) {
                if ($this->upsertResource($store, $product, $resourcePayload)) {
                    $imagesDownloaded++;
                }
                $resources++;
            }
        }

        return [
            'store' => $store,
            'catalogue' => $catalogue,
            'products' => $products,
            'resources' => $resources,
            'prices' => $prices,
            'images_downloaded' => $imagesDownloaded,
        ];
    }

    private function upsertStore(array $payload): Store
    {
        $slug = (string) ($payload['slug'] ?? Str::slug($payload['name'] ?? 'webcatalogue-store'));

        return Store::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $payload['name'] ?? Str::title(str_replace('-', ' ', $slug)),
                'code' => $payload['code'] ?? strtoupper(str_replace('-', '_', $slug)),
                'domain' => $payload['domain'] ?? null,
                'status' => $payload['status'] ?? 'active',
                'logo_path' => $payload['logo_path'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
            ]
        );
    }

    private function upsertCatalogue(Store $store, array $payload): Catalogue
    {
        $slug = (string) ($payload['slug'] ?? Str::slug($payload['name'] ?? 'catalogue'));

        return Catalogue::updateOrCreate(
            ['id_store' => $store->id, 'slug' => $slug],
            [
                'name' => $payload['name'] ?? Str::title(str_replace('-', ' ', $slug)),
                'description' => $payload['description'] ?? null,
                'catalogue_type' => $payload['catalogue_type'] ?? 'catalogue',
                'show_prices' => (bool) ($payload['show_prices'] ?? true),
                'price_mode' => $payload['price_mode'] ?? 'reference',
                'visibility' => $payload['visibility'] ?? 'public',
                'status' => $payload['status'] ?? 'active',
                'published_at' => $payload['published_at'] ?? now(),
                'metadata' => $payload['metadata'] ?? null,
            ]
        );
    }

    private function upsertProduct(Store $store, array $payload): Product
    {
        $reference = (string) ($payload['reference'] ?? Str::upper(Str::slug($payload['name'] ?? Str::uuid(), '-')));

        return Product::updateOrCreate(
            ['id_store' => $store->id, 'reference' => $reference],
            [
                'external_id' => $payload['external_id'] ?? null,
                'external_source' => $payload['external_source'] ?? null,
                'sku' => $payload['sku'] ?? null,
                'ean13' => $payload['ean13'] ?? null,
                'name' => $payload['name'] ?? $reference,
                'slug' => $payload['slug'] ?? Str::slug($reference . '-' . ($payload['name'] ?? 'product')),
                'short_description' => $payload['short_description'] ?? null,
                'description' => $payload['description'] ?? null,
                'brand' => $payload['brand'] ?? null,
                'category' => $payload['category'] ?? null,
                'price' => $payload['price'] ?? null,
                'currency' => $payload['currency'] ?? config('webcatalogue.default_currency', 'EUR'),
                'status' => $payload['status'] ?? 'active',
                'metadata' => $payload['metadata'] ?? null,
            ]
        );
    }

    private function attachToCatalogue(Product $product, Catalogue $catalogue, int $sortOrder): void
    {
        $product->catalogues()->syncWithoutDetaching([
            $catalogue->id => [
                'id_store' => $catalogue->id_store,
                'sort_order' => $sortOrder,
                'is_featured' => false,
                'status' => 'active',
                'metadata' => null,
            ],
        ]);
    }

    private function upsertPrice(Store $store, Product $product, array $payload): void
    {
        ProductPrice::updateOrCreate(
            [
                'id_store' => $store->id,
                'id_product' => $product->id,
                'price_type' => $payload['price_type'] ?? 'reference',
                'currency' => $payload['currency'] ?? config('webcatalogue.default_currency', 'EUR'),
            ],
            [
                'regular_price' => $payload['regular_price'] ?? null,
                'sale_price' => $payload['sale_price'] ?? null,
                'tax_included' => (bool) ($payload['tax_included'] ?? true),
                'status' => $payload['status'] ?? 'active',
                'metadata' => $payload['metadata'] ?? null,
            ]
        );
    }

    private function upsertResource(Store $store, Product $product, array $payload): bool
    {
        $sourceUrl = $payload['source_url'] ?? null;
        $resourceType = $payload['resource_type'] ?? 'download';
        $filePath = $payload['file_path'] ?? null;
        $downloaded = false;

        if (!empty($payload['local_public_path'])) {
            $localPublicPath = ltrim((string) $payload['local_public_path'], '/');
            $absolutePath = public_path($localPublicPath);
            $extension = pathinfo($absolutePath, PATHINFO_EXTENSION) ?: ($payload['extension'] ?? 'bin');
            $filePath = $filePath ?: $this->resourceStoragePath($store, $product, $resourceType, $extension);

            if (is_file($absolutePath) && (($payload['refresh'] ?? false) || !Storage::disk('public')->exists($filePath))) {
                Storage::disk('public')->put($filePath, file_get_contents($absolutePath));
                $downloaded = true;
            }
        } elseif ($sourceUrl && ($payload['download'] ?? false)) {
            $extension = pathinfo(parse_url($sourceUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: ($payload['extension'] ?? 'bin');
            $filePath = $filePath ?: $this->resourceStoragePath($store, $product, $resourceType, $extension);

            if (($payload['refresh'] ?? false) || !Storage::disk('public')->exists($filePath)) {
                $binary = Http::withHeaders($payload['headers'] ?? [])->timeout(60)->get($sourceUrl)->throw()->body();
                Storage::disk('public')->put($filePath, $binary);
                $downloaded = true;
            }
        }

        Resource::updateOrCreate(
            [
                'id_store' => $store->id,
                'id_product' => $product->id,
                'resource_type' => $resourceType,
                'source_url' => $sourceUrl,
            ],
            [
                'resource_owner_type' => 'product',
                'resource_owner_id' => $product->id,
                'title' => $payload['title'] ?? $product->reference . ' resource',
                'description' => $payload['description'] ?? null,
                'source_type' => $payload['source_type'] ?? ($sourceUrl ? 'external_import' : 'manual'),
                'file_path' => $filePath,
                'public_url' => $filePath ? '/storage/' . ltrim($filePath, '/') : ($payload['public_url'] ?? null),
                'filename' => $filePath ? basename($filePath) : ($payload['filename'] ?? null),
                'mime_type' => $payload['mime_type'] ?? null,
                'file_size' => $filePath && Storage::disk('public')->exists($filePath) ? Storage::disk('public')->size($filePath) : ($payload['file_size'] ?? null),
                'extension' => $payload['extension'] ?? ($filePath ? pathinfo($filePath, PATHINFO_EXTENSION) : null),
                'is_main' => (bool) ($payload['is_main'] ?? false),
                'sort_order' => (int) ($payload['sort_order'] ?? 0),
                'status' => $payload['status'] ?? 'active',
                'metadata' => $payload['metadata'] ?? null,
            ]
        );

        return $downloaded;
    }

    private function folderForType(string $resourceType): string
    {
        return match ($resourceType) {
            'image', 'gallery_image', 'thumbnail', 'cover', 'logo' => 'images',
            'manual', 'datasheet', 'assembly_instructions', 'download' => 'documents',
            'video' => 'videos',
            'model_3d' => 'models',
            'ar_file' => 'ar',
            'vr_file', 'vr_scene' => 'vr',
            default => 'assets',
        };
    }

    private function resourceStoragePath(Store $store, Product $product, string $resourceType, string $extension): string
    {
        return 'webcatalogue/stores/' . $store->id
            . '/products/' . $product->id
            . '/' . $this->folderForType($resourceType)
            . '/' . Str::slug($product->reference) . '.' . strtolower($extension);
    }
}
