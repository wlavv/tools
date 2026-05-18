<?php

namespace Modules\WebCatalogue\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Recognition\ProductIdentifierService;

class SeedRecognitionTestProductsCommand extends Command
{
    protected $signature = 'webcatalogue:recognition-seed-test-products
        {--store= : Store id}
        {--q=oriflame : Store search text when --store is not provided}';

    protected $description = 'Create WebCatalogue products with reference, SKU, EAN and QR metadata for recognition tests.';

    public function handle(ProductIdentifierService $identifiers): int
    {
        $store = $this->resolveStore();
        if (!$store) {
            return self::FAILURE;
        }

        $rows = $this->testProducts();
        $created = 0;
        $updated = 0;
        $synced = 0;

        foreach ($rows as $row) {
            $product = Product::query()->where('id_store', $store->id)->where('reference', $row['reference'])->first();
            $wasExisting = (bool) $product;
            $slug = $this->uniqueSlug($store, $row['name'], $product?->id);

            $product = Product::updateOrCreate(
                ['id_store' => $store->id, 'reference' => $row['reference']],
                [
                    'external_id' => $row['external_id'],
                    'external_source' => 'recognition_test_seed',
                    'sku' => $row['sku'],
                    'ean13' => $row['ean13'],
                    'name' => $row['name'],
                    'slug' => $slug,
                    'short_description' => $row['short_description'],
                    'description' => $row['description'],
                    'brand' => 'Oriflame',
                    'category' => $row['category'],
                    'price' => $row['price'],
                    'currency' => 'EUR',
                    'stock' => 10,
                    'status' => 'active',
                    'metadata' => [
                        'recognition_test' => true,
                        'qr_url' => $row['qr_url'],
                        'barcode' => $row['ean13'],
                        'manufacturer_code' => $row['manufacturer_code'],
                        'seeded_at' => now()->toIso8601String(),
                    ],
                ]
            );

            $result = $identifiers->syncProduct($product);
            $synced += (int) ($result['synced'] ?? 0);
            $wasExisting ? $updated++ : $created++;

            $this->line(sprintf(
                '%s #%d | ref=%s | sku=%s | ean13=%s',
                $wasExisting ? 'Updated' : 'Created',
                $product->id,
                $product->reference,
                $product->sku,
                $product->ean13
            ));
        }

        $this->info('Seeded recognition test products for store #' . $store->id . ' - ' . $store->name);
        $this->line('Created: ' . $created);
        $this->line('Updated: ' . $updated);
        $this->line('Identifiers synced: ' . $synced);

        return self::SUCCESS;
    }

    private function resolveStore(): ?Store
    {
        $storeId = (int) $this->option('store');
        if ($storeId > 0) {
            $store = Store::query()->find($storeId);
            if (!$store) {
                $this->error('Store not found: ' . $storeId);
            }

            return $store;
        }

        $query = trim((string) $this->option('q'));
        $stores = Store::query()
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', '%' . $query . '%')
                    ->orWhere('slug', 'like', '%' . $query . '%');
            })
            ->orderBy('id')
            ->limit(10)
            ->get();

        if ($stores->isEmpty()) {
            $this->error('No store found for search: ' . $query);
            return null;
        }

        if ($stores->count() > 1) {
            $this->warn('Multiple stores found; using the first. Pass --store=ID to choose another.');
            foreach ($stores as $candidate) {
                $this->line($candidate->id . ' | ' . $candidate->name . ' | ' . $candidate->slug . ' | ' . $candidate->status);
            }
        }

        return $stores->first();
    }

    private function uniqueSlug(Store $store, string $name, ?int $currentProductId = null): string
    {
        $base = Str::slug($name) ?: 'recognition-test-product';
        $slug = $base;
        $suffix = 2;

        while (Product::query()
            ->where('id_store', $store->id)
            ->where('slug', $slug)
            ->when($currentProductId, fn ($query) => $query->where('id', '!=', $currentProductId))
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function testProducts(): array
    {
        return [
            [
                'reference' => 'ORI-LS-QR-001',
                'sku' => 'LS-ORI-SKU-001',
                'ean13' => '5601234500017',
                'external_id' => 'ORIFLAME-LS-001',
                'manufacturer_code' => 'ORI-MFG-001',
                'qr_url' => 'https://example.test/oriflame/products?ref=ORI-LS-QR-001&sku=LS-ORI-SKU-001',
                'name' => 'Oriflame LS Recognition Test Cream',
                'category' => 'Skin care',
                'price' => 12.9000,
                'short_description' => 'Recognition test product with QR, SKU and EAN13.',
                'description' => 'Seeded product for validating QR, reference, SKU and EAN13 matching.',
            ],
            [
                'reference' => 'ORI-LS-QR-002',
                'sku' => 'LS-ORI-SKU-002',
                'ean13' => '5601234500024',
                'external_id' => 'ORIFLAME-LS-002',
                'manufacturer_code' => 'ORI-MFG-002',
                'qr_url' => 'https://example.test/oriflame/ORI-LS-QR-002',
                'name' => 'Oriflame LS Recognition Test Serum',
                'category' => 'Skin care',
                'price' => 18.5000,
                'short_description' => 'Recognition test serum with QR URL path reference.',
                'description' => 'Seeded product for validating QR URLs where the product reference is in the path.',
            ],
            [
                'reference' => 'ORI-LS-QR-003',
                'sku' => 'LS-ORI-SKU-003',
                'ean13' => '5601234500031',
                'external_id' => 'ORIFLAME-LS-003',
                'manufacturer_code' => 'ORI-MFG-003',
                'qr_url' => 'LS-ORI-SKU-003',
                'name' => 'Oriflame LS Recognition Test Mascara',
                'category' => 'Makeup',
                'price' => 9.9900,
                'short_description' => 'Recognition test product where QR content is a SKU.',
                'description' => 'Seeded product for validating QR content that carries only an internal SKU.',
            ],
            [
                'reference' => 'ORI-LS-QR-004',
                'sku' => 'LS-ORI-SKU-004',
                'ean13' => '5601234500048',
                'external_id' => 'ORIFLAME-LS-004',
                'manufacturer_code' => 'ORI-MFG-004',
                'qr_url' => '5601234500048',
                'name' => 'Oriflame LS Recognition Test Fragrance',
                'category' => 'Fragrance',
                'price' => 24.9000,
                'short_description' => 'Recognition test product where QR content is the EAN13.',
                'description' => 'Seeded product for validating QR content that carries only a numeric barcode value.',
            ],
        ];
    }
}
