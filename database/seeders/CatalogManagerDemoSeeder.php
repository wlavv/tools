<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CatalogManagerDemoSeeder extends Seeder
{
    public function run(): void
    {
        $requiredTables = [
            'catalog_core_manufacturers',
            'catalog_core_suppliers',
            'catalog_core_products',
            'catalog_core_product_suppliers',
            'catalog_stores',
            'catalog_store_products',
            'catalog_store_product_lang',
            'catalog_store_categories',
            'catalog_store_category_lang',
            'catalog_store_product_categories',
            'catalog_store_prices',
        ];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $this->command?->warn("Skipping CatalogManager demo seed: missing table {$table}.");
                return;
            }
        }

        $now = now();

        $manufacturers = [
            ['name' => 'DEMO LSG Home', 'slug' => 'demo-lsg-home', 'website' => 'https://demo.lsg-home.test'],
            ['name' => 'DEMO Atlas Living', 'slug' => 'demo-atlas-living', 'website' => 'https://demo.atlas-living.test'],
            ['name' => 'DEMO NovaTech', 'slug' => 'demo-novatech', 'website' => 'https://demo.novatech.test'],
        ];

        foreach ($manufacturers as $manufacturer) {
            DB::table('catalog_core_manufacturers')->updateOrInsert(
                ['slug' => $manufacturer['slug']],
                array_merge($manufacturer, ['active' => true, 'updated_at' => $now, 'created_at' => $now])
            );
        }

        $manufacturerIds = DB::table('catalog_core_manufacturers')
            ->whereIn('slug', array_column($manufacturers, 'slug'))
            ->pluck('id', 'slug');

        $suppliers = [
            ['name' => 'DEMO Iberia Supplies', 'code' => 'DEMO-IBERIA', 'email' => 'sales@demo-iberia.test', 'phone' => '+351 210 000 100', 'currency' => 'EUR', 'lead_time_days' => 4],
            ['name' => 'DEMO EU Warehouse', 'code' => 'DEMO-EU-WH', 'email' => 'ops@demo-eu-warehouse.test', 'phone' => '+351 220 000 200', 'currency' => 'EUR', 'lead_time_days' => 9],
            ['name' => 'DEMO Dropship Partner', 'code' => 'DEMO-DROP', 'email' => 'catalog@demo-dropship.test', 'phone' => '+351 230 000 300', 'currency' => 'EUR', 'lead_time_days' => 12],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('catalog_core_suppliers')->updateOrInsert(
                ['code' => $supplier['code']],
                array_merge($supplier, ['active' => true, 'updated_at' => $now, 'created_at' => $now])
            );
        }

        $supplierIds = DB::table('catalog_core_suppliers')
            ->whereIn('code', array_column($suppliers, 'code'))
            ->pluck('id', 'code');

        $stores = [
            ['code' => 'DEMO-PT', 'name' => 'DEMO LSG Portugal', 'domain' => 'demo-pt.webtools-manager.test', 'locale' => 'pt', 'currency' => 'EUR'],
            ['code' => 'DEMO-ES', 'name' => 'DEMO LSG Spain', 'domain' => 'demo-es.webtools-manager.test', 'locale' => 'es', 'currency' => 'EUR'],
        ];

        foreach ($stores as $store) {
            DB::table('catalog_stores')->updateOrInsert(
                ['code' => $store['code']],
                array_merge($store, [
                    'active' => true,
                    'settings' => json_encode(['demo' => true, 'channel' => 'validation']),
                    'updated_at' => $now,
                    'created_at' => $now,
                ])
            );
        }

        $storeIds = DB::table('catalog_stores')
            ->whereIn('code', array_column($stores, 'code'))
            ->pluck('id', 'code');

        $categoryMap = [];
        foreach ($storeIds as $storeCode => $storeId) {
            foreach ([
                ['code' => 'DEMO-HOME', 'name' => 'Demo Home Essentials'],
                ['code' => 'DEMO-OFFICE', 'name' => 'Demo Office & Productivity'],
                ['code' => 'DEMO-TECH', 'name' => 'Demo Smart Tech'],
            ] as $index => $category) {
                DB::table('catalog_store_categories')->updateOrInsert(
                    ['store_id' => $storeId, 'code' => $category['code']],
                    ['active' => true, 'position' => $index + 1, 'updated_at' => $now, 'created_at' => $now]
                );

                $categoryId = DB::table('catalog_store_categories')
                    ->where('store_id', $storeId)
                    ->where('code', $category['code'])
                    ->value('id');

                DB::table('catalog_store_category_lang')->updateOrInsert(
                    ['store_category_id' => $categoryId, 'locale' => $storeCode === 'DEMO-ES' ? 'es' : 'pt'],
                    [
                        'name' => $category['name'],
                        'description' => 'Categoria demo para validar listagens, filtros e matriz de publicação.',
                        'link_rewrite' => Str::slug($category['name']),
                        'meta_title' => $category['name'],
                        'meta_description' => 'Dados dummy do CatalogManager.',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                $categoryMap[$storeCode][$category['code']] = $categoryId;
            }
        }

        $products = [
            [
                'reference' => 'DEMO-LSG-CHAIR-001',
                'ean13' => '5600000000011',
                'name' => 'DEMO Ergonomic Work Chair',
                'manufacturer_slug' => 'demo-lsg-home',
                'status' => 'active',
                'housing' => 'boxed',
                'type' => 'simple',
                'weight' => 12.8,
                'category' => 'DEMO-OFFICE',
                'price' => 189.9,
                'cost' => 102.4,
                'supplier' => 'DEMO-IBERIA',
                'complete' => true,
            ],
            [
                'reference' => 'DEMO-LSG-DESK-002',
                'ean13' => '5600000000028',
                'name' => 'DEMO Standing Desk Compact',
                'manufacturer_slug' => 'demo-atlas-living',
                'status' => 'active',
                'housing' => 'flatpack',
                'type' => 'simple',
                'weight' => 28.5,
                'category' => 'DEMO-OFFICE',
                'price' => 329.0,
                'cost' => 210.0,
                'supplier' => 'DEMO-EU-WH',
                'complete' => true,
            ],
            [
                'reference' => 'DEMO-LSG-LAMP-003',
                'ean13' => '5600000000035',
                'name' => 'DEMO Smart Desk Lamp',
                'manufacturer_slug' => 'demo-novatech',
                'status' => 'active',
                'housing' => 'boxed',
                'type' => 'simple',
                'weight' => 1.4,
                'category' => 'DEMO-TECH',
                'price' => 59.9,
                'cost' => 28.5,
                'supplier' => 'DEMO-DROP',
                'complete' => true,
            ],
            [
                'reference' => 'DEMO-LSG-SOFA-004',
                'ean13' => null,
                'name' => 'DEMO Modular Sofa Draft',
                'manufacturer_slug' => 'demo-atlas-living',
                'status' => 'draft',
                'housing' => null,
                'type' => 'configurable',
                'weight' => 54.0,
                'category' => 'DEMO-HOME',
                'price' => null,
                'cost' => 470.0,
                'supplier' => 'DEMO-EU-WH',
                'complete' => false,
            ],
            [
                'reference' => 'DEMO-LSG-SHELF-005',
                'ean13' => '5600000000059',
                'name' => 'DEMO Wall Shelf Missing Supplier',
                'manufacturer_slug' => 'demo-lsg-home',
                'status' => 'review',
                'housing' => 'flatpack',
                'type' => 'simple',
                'weight' => 6.2,
                'category' => 'DEMO-HOME',
                'price' => 44.5,
                'cost' => null,
                'supplier' => null,
                'complete' => false,
            ],
            [
                'reference' => 'DEMO-LSG-UNKNOWN-006',
                'ean13' => null,
                'name' => 'DEMO Product Missing Manufacturer',
                'manufacturer_slug' => null,
                'status' => 'draft',
                'housing' => null,
                'type' => 'simple',
                'weight' => null,
                'category' => null,
                'price' => null,
                'cost' => null,
                'supplier' => null,
                'complete' => false,
            ],
        ];

        foreach ($products as $index => $product) {
            $manufacturerId = $product['manufacturer_slug'] ? ($manufacturerIds[$product['manufacturer_slug']] ?? null) : null;

            DB::table('catalog_core_products')->updateOrInsert(
                ['reference' => $product['reference']],
                [
                    'internal_sku' => 'WT-' . $product['reference'],
                    'ean13' => $product['ean13'],
                    'name' => $product['name'],
                    'manufacturer_id' => $manufacturerId,
                    'type' => $product['type'],
                    'status' => $product['status'],
                    'weight' => $product['weight'],
                    'width' => 40 + $index,
                    'height' => 20 + $index,
                    'depth' => 35 + $index,
                    'housing' => $product['housing'],
                    'internal_notes' => 'DEMO data generated for CatalogManager validation.',
                    'updated_at' => $now,
                    'created_at' => $now,
                    'deleted_at' => null,
                ]
            );

            $productId = DB::table('catalog_core_products')->where('reference', $product['reference'])->value('id');

            if ($product['supplier']) {
                DB::table('catalog_core_product_suppliers')->updateOrInsert(
                    ['product_id' => $productId, 'supplier_id' => $supplierIds[$product['supplier']]],
                    [
                        'supplier_reference' => $product['reference'] . '-SUP',
                        'cost' => $product['cost'],
                        'currency' => 'EUR',
                        'moq' => $index + 1,
                        'is_default' => true,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            foreach ($storeIds as $storeCode => $storeId) {
                $isCompleteInStore = $product['complete'] || ($storeCode === 'DEMO-PT' && $index < 4);

                DB::table('catalog_store_products')->updateOrInsert(
                    ['product_id' => $productId, 'store_id' => $storeId],
                    [
                        'status' => $isCompleteInStore ? 'ready' : 'draft',
                        'active' => $isCompleteInStore,
                        'visible' => $isCompleteInStore,
                        'available_for_order' => $isCompleteInStore && $product['price'] !== null,
                        'is_published' => $isCompleteInStore && $index < 3,
                        'published_at' => $isCompleteInStore && $index < 3 ? $now->copy()->subDays($index + 1) : null,
                        'position' => $index + 1,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );

                $storeProductId = DB::table('catalog_store_products')
                    ->where('product_id', $productId)
                    ->where('store_id', $storeId)
                    ->value('id');

                if ($isCompleteInStore || $index < 4) {
                    $locale = $storeCode === 'DEMO-ES' ? 'es' : 'pt';
                    DB::table('catalog_store_product_lang')->updateOrInsert(
                        ['store_product_id' => $storeProductId, 'locale' => $locale],
                        [
                            'name' => $storeCode === 'DEMO-ES' ? str_replace('DEMO', 'DEMO ES', $product['name']) : $product['name'],
                            'description_short' => 'Resumo demo para validar conteúdo de loja.',
                            'description' => 'Descrição longa dummy com detalhes comerciais, logística e SEO para validar o CatalogManager.',
                            'meta_title' => $product['name'],
                            'meta_description' => 'Meta description demo para ' . $product['name'],
                            'link_rewrite' => Str::slug($product['name']),
                            'keywords' => 'demo,catalog,lsg,validation',
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }

                if ($product['price'] !== null && ($isCompleteInStore || $index < 5)) {
                    DB::table('catalog_store_prices')->updateOrInsert(
                        ['store_product_id' => $storeProductId],
                        [
                            'price' => $storeCode === 'DEMO-ES' ? $product['price'] + 4 : $product['price'],
                            'sale_price' => $index < 2 ? $product['price'] - 15 : null,
                            'cost_snapshot' => $product['cost'],
                            'currency' => 'EUR',
                            'status' => 'active',
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }

                if ($product['category'] && isset($categoryMap[$storeCode][$product['category']]) && ($isCompleteInStore || $index < 5)) {
                    DB::table('catalog_store_product_categories')->updateOrInsert(
                        ['store_product_id' => $storeProductId, 'store_category_id' => $categoryMap[$storeCode][$product['category']]],
                        [
                            'is_default' => true,
                            'position' => $index + 1,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }

                if (Schema::hasTable('catalog_store_visibility_rules')) {
                    DB::table('catalog_store_visibility_rules')->updateOrInsert(
                        ['store_product_id' => $storeProductId],
                        [
                            'visible' => $isCompleteInStore,
                            'searchable' => $isCompleteInStore,
                            'available_for_order' => $isCompleteInStore && $product['price'] !== null,
                            'show_price' => $product['price'] !== null,
                            'scheduled_from' => null,
                            'scheduled_to' => null,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ]
                    );
                }
            }

            if (Schema::hasTable('catalog_prestashop_sync_queue')) {
                DB::table('catalog_prestashop_sync_queue')->updateOrInsert(
                    ['entity_type' => 'product', 'entity_id' => $productId, 'operation' => 'demo_sync'],
                    [
                        'status' => $index < 3 ? 'pending' : 'processed',
                        'payload' => json_encode(['reference' => $product['reference'], 'demo' => true]),
                        'last_error' => $index === 4 ? 'Demo warning: supplier mapping missing.' : null,
                        'attempts' => $index === 4 ? 1 : 0,
                        'processed_at' => $index < 3 ? null : $now,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            if (Schema::hasTable('catalog_ai_generations')) {
                DB::table('catalog_ai_generations')->updateOrInsert(
                    ['product_id' => $productId, 'type' => 'demo_description'],
                    [
                        'store_product_id' => null,
                        'status' => 'generated',
                        'input_payload' => json_encode(['name' => $product['name']]),
                        'output_payload' => json_encode(['description' => 'AI-generated demo copy for ' . $product['name']]),
                        'applied' => $index < 2,
                        'applied_at' => $index < 2 ? $now : null,
                        'user_id' => null,
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }

            if (Schema::hasTable('catalog_logs_activity')) {
                $logExists = DB::table('catalog_logs_activity')
                    ->where('subject_type', 'product')
                    ->where('subject_id', $productId)
                    ->where('action', 'demo_seeded')
                    ->exists();

                if (!$logExists) {
                    DB::table('catalog_logs_activity')->insert([
                        'subject_type' => 'product',
                        'subject_id' => $productId,
                        'action' => 'demo_seeded',
                        'old_values' => null,
                        'new_values' => json_encode(['reference' => $product['reference']]),
                        'user_id' => null,
                        'ip' => '127.0.0.1',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        if (Schema::hasTable('catalog_store_pagespeed_insights')) {
            foreach ($storeIds as $storeCode => $storeId) {
                DB::table('catalog_store_pagespeed_insights')->updateOrInsert(
                    ['store_id' => $storeId, 'checked_on' => $now->toDateString(), 'strategy' => 'mobile'],
                    [
                        'url' => 'https://' . strtolower($storeCode) . '.demo.test',
                        'status' => 'completed',
                        'performance_score' => $storeCode === 'DEMO-PT' ? 88 : 73,
                        'accessibility_score' => $storeCode === 'DEMO-PT' ? 94 : 84,
                        'best_practices_score' => $storeCode === 'DEMO-PT' ? 91 : 79,
                        'seo_score' => $storeCode === 'DEMO-PT' ? 97 : 86,
                        'first_contentful_paint_ms' => $storeCode === 'DEMO-PT' ? 1120 : 1650,
                        'largest_contentful_paint_ms' => $storeCode === 'DEMO-PT' ? 2100 : 3100,
                        'total_blocking_time_ms' => $storeCode === 'DEMO-PT' ? 80 : 210,
                        'cumulative_layout_shift' => $storeCode === 'DEMO-PT' ? 4 : 12,
                        'speed_index_ms' => $storeCode === 'DEMO-PT' ? 2400 : 3900,
                        'error_message' => null,
                        'raw_summary' => json_encode(['demo' => true, 'source' => 'CatalogManagerDemoSeeder']),
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }

        $this->command?->info('CatalogManager demo data seeded: manufacturers, suppliers, stores, products, categories, prices, content, sync queue and PSI metrics.');
    }
}
