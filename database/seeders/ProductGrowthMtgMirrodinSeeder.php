<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductGrowthMtgMirrodinSeeder extends Seeder
{
    private const SET_CODE = 'mrd';
    private const BRAND_SLUG = 'magic-the-gathering';
    private const SUPPLIER_SLUG = 'wizards';
    private const STORE_SLUG = 'tcg-collectors';

    public function run(): void
    {
        foreach ([
            'mtg_cards',
            'lsg_catalog_core_products',
            'catalog_core_manufacturers',
            'catalog_core_suppliers',
            'lsg_sites',
            'lsg_catalog_store_products',
            'lsg_catalog_product_assets',
        ] as $table) {
            if (!Schema::hasTable($table)) {
                $this->command?->warn("Tabela em falta: {$table}");
                return;
            }
        }

        $now = now();
        $cards = DB::table('mtg_cards')
            ->where('set_code', self::SET_CODE)
            ->get()
            ->sortBy(fn ($card) => $this->collectorSortValue((string) $card->collector_number))
            ->values();

        if ($cards->isEmpty()) {
            $this->command?->warn('Nao foram encontradas cartas MTG para o set mrd.');
            return;
        }

        $set = null;

        if (Schema::hasTable('mtg_sets')) {
            $set = DB::table('mtg_sets')->where('sub_set_code', self::SET_CODE)->first();

            if (!$set) {
                $set = DB::table('mtg_sets')
                    ->where('set_code', self::SET_CODE)
                    ->whereColumn('set_code', 'sub_set_code')
                    ->first();
            }
        }

        $setName = $cards->first()?->set_name ?: ($set?->set_name ?: 'Mirrodin');
        $setCode = strtoupper(self::SET_CODE);

        $brandId = $this->upsertBrand($now);
        $supplierId = $this->upsertSupplier($now);
        $storeId = $this->upsertStore($now);

        $workflowSteps = $this->workflowSteps($setName, $cards->count());
        $setProductId = $this->upsertSetProduct($set, $setName, $setCode, $brandId, $supplierId, $cards, $workflowSteps, $now);
        $setStoreProductId = $this->upsertStoreProduct(
            $setProductId,
            $storeId,
            "{$setName} Complete Set",
            "{$setName} - set completo Magic The Gathering com {$cards->count()} cartas.",
            "Colecao completa {$setName}, preparada para WebCatalogue e PrestaShop.",
            $cards->sum(fn ($card) => (float) ($card->price ?: 0.05)),
            $cards->count(),
            [
                'listing_type' => 'set',
                'campaign' => 'mtg-mirrodin',
                'source' => 'mtg_cards',
            ],
            $now
        );

        if ($set?->icon_svg_uri) {
            $this->upsertAsset(
                $setProductId,
                $setStoreProductId,
                $storeId,
                'image',
                'set_logo',
                'mtg',
                $set?->id,
                "{$setName} logo",
                $set->icon_svg_uri,
                'image/svg+xml',
                'svg',
                true,
                true,
                95,
                1,
                ['set_code' => self::SET_CODE],
                $now
            );
        }

        $createdCards = 0;

        foreach ($cards as $index => $card) {
            $collector = (string) $card->collector_number;
            $reference = "{$setCode}-" . $this->collectorReference($collector);
            $price = (float) ($card->price ?: 0.05);
            $imageUrl = $card->image_url ?: null;
            $ad = $this->adPayload($card, $setName, $reference);
            $description = $this->cardDescription($card, $setName);

            DB::table('lsg_catalog_core_products')->updateOrInsert(
                ['internal_sku' => "MTG-{$reference}"],
                [
                    'reference' => $reference,
                    'mpn' => $reference,
                    'brand_id' => $brandId,
                    'supplier_id' => $supplierId,
                    'name' => $card->name,
                    'description' => $description,
                    'product_type' => 'tcg_card',
                    'base_cost' => null,
                    'base_price' => $price,
                    'status' => 'ready_to_sync',
                    'data_quality_score' => $imageUrl ? 96.00 : 84.00,
                    'is_active' => true,
                    'metadata' => json_encode([
                        'source' => 'mtg_cards',
                        'game' => 'Magic The Gathering',
                        'set_code' => self::SET_CODE,
                        'set_name' => $setName,
                        'set_product_sku' => "MTG-{$setCode}-SET",
                        'collector_number' => $collector,
                        'rarity' => $card->rarity,
                        'mana_cost' => $card->mana_cost,
                        'card_type' => $card->card_type,
                        'color_group' => $card->color_group,
                        'power' => $card->power,
                        'toughness' => $card->toughness,
                        'artist' => $card->artist,
                        'scryfall_uri' => $card->scryfall_uri,
                        'source_mtg_card_id' => $card->id,
                        'workflow_steps' => $workflowSteps,
                        'ad_listing' => $ad,
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $productId = DB::table('lsg_catalog_core_products')->where('internal_sku', "MTG-{$reference}")->value('id');
            $storeProductId = $this->upsertStoreProduct(
                $productId,
                $storeId,
                "{$card->name} ({$setCode} {$collector})",
                trim(($card->card_type ?: 'Magic The Gathering card') . ' - ' . ($card->rarity ?: 'rarity unknown')),
                $description,
                $price,
                1,
                [
                    'listing_type' => 'single_card_ad',
                    'campaign' => 'mtg-mirrodin',
                    'ad' => $ad,
                    'set_product_id' => $setProductId,
                ],
                $now
            );

            if ($imageUrl) {
                $this->upsertAsset(
                    $productId,
                    $storeProductId,
                    $storeId,
                    'image',
                    'card_image',
                    'mtg',
                    $card->id,
                    "{$card->name} card image",
                    $imageUrl,
                    'image/jpeg',
                    'jpg',
                    true,
                    true,
                    96,
                    1,
                    [
                        'set_code' => self::SET_CODE,
                        'collector_number' => $collector,
                        'local_file_exists' => file_exists(public_path(ltrim($imageUrl, '/'))),
                    ],
                    $now
                );

                $this->upsertAsset(
                    $productId,
                    $storeProductId,
                    $storeId,
                    'image',
                    'ad_creative',
                    'ai-ads-manager',
                    $card->id,
                    "{$card->name} ad creative",
                    $imageUrl,
                    'image/jpeg',
                    'jpg',
                    true,
                    true,
                    94,
                    2,
                    $ad,
                    $now
                );
            }

            $this->replaceLogs($productId, $card, $ad, $now);
            $createdCards++;
        }

        $this->command?->info("Set {$setName} criado/atualizado no Product Growth.");
        $this->command?->info("Produto agregado: MTG-{$setCode}-SET");
        $this->command?->info("Cartas/anuncios processados: {$createdCards}");
    }

    private function upsertBrand($now): int
    {
        DB::table('catalog_core_manufacturers')->updateOrInsert(
            ['slug' => self::BRAND_SLUG],
            [
                'name' => 'Magic The Gathering',
                'website' => 'https://magic.wizards.com',
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) DB::table('catalog_core_manufacturers')->where('slug', self::BRAND_SLUG)->value('id');
    }

    private function upsertSupplier($now): int
    {
        DB::table('catalog_core_suppliers')->updateOrInsert(
            ['code' => 'WIZARDS'],
            [
                'name' => 'Wizards',
                'email' => null,
                'phone' => null,
                'currency' => 'EUR',
                'lead_time_days' => null,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) DB::table('catalog_core_suppliers')->where('code', 'WIZARDS')->value('id');
    }

    private function upsertStore($now): int
    {
        DB::table('lsg_sites')->updateOrInsert(
            ['slug' => self::STORE_SLUG],
            [
                'name' => 'TCG Collectors',
                'site_type' => 'store',
                'domain' => 'tcg-collectors.local',
                'public_url' => 'http://tcg-collectors.local',
                'environment' => 'production',
                'status' => 'active',
                'default_language' => 'pt',
                'default_currency' => 'EUR',
                'settings' => json_encode([
                    'channel' => 'tcg',
                    'source' => 'mtg_cards',
                    'prestashop_shop_id' => null,
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) DB::table('lsg_sites')->where('slug', self::STORE_SLUG)->value('id');
    }

    private function upsertSetProduct($set, string $setName, string $setCode, int $brandId, int $supplierId, $cards, array $workflowSteps, $now): int
    {
        DB::table('lsg_catalog_core_products')->updateOrInsert(
            ['internal_sku' => "MTG-{$setCode}-SET"],
            [
                'reference' => "MTG-{$setCode}",
                'mpn' => "MTG-{$setCode}-SET",
                'brand_id' => $brandId,
                'supplier_id' => $supplierId,
                'name' => "{$setName} Complete Set",
                'description' => "{$setName} completo para Magic The Gathering, criado como produto agregado no Product Growth.",
                'product_type' => 'tcg_set',
                'base_price' => $cards->sum(fn ($card) => (float) ($card->price ?: 0.05)),
                'status' => 'ready_to_sync',
                'data_quality_score' => 94.00,
                'is_active' => true,
                'metadata' => json_encode([
                    'source' => 'mtg_sets',
                    'game' => 'Magic The Gathering',
                    'set_code' => self::SET_CODE,
                    'set_name' => $setName,
                    'set_type' => $set?->set_type,
                    'released_at' => $set?->released_at,
                    'card_count' => $cards->count(),
                    'scryfall_uri' => $set?->scryfall_uri,
                    'workflow_steps' => $workflowSteps,
                ]),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) DB::table('lsg_catalog_core_products')->where('internal_sku', "MTG-{$setCode}-SET")->value('id');
    }

    private function upsertStoreProduct(int $productId, int $storeId, string $name, string $shortDescription, string $description, float $price, int $stock, array $overrides, $now): int
    {
        DB::table('lsg_catalog_store_products')->updateOrInsert(
            ['product_id' => $productId, 'store_id' => $storeId],
            [
                'name' => $name,
                'short_description' => Str::limit($shortDescription, 240, ''),
                'description' => $description,
                'seo_title' => Str::limit($name . ' | Magic The Gathering', 255, ''),
                'seo_description' => Str::limit($shortDescription, 255, ''),
                'sale_price' => $price,
                'cost_price' => null,
                'margin_percentage' => null,
                'stock_quantity' => $stock,
                'active_for_sale' => true,
                'sync_to_prestashop' => true,
                'sync_status' => 'ready_to_sync',
                'store_overrides' => json_encode($overrides),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        return (int) DB::table('lsg_catalog_store_products')->where(['product_id' => $productId, 'store_id' => $storeId])->value('id');
    }

    private function upsertAsset(
        int $productId,
        ?int $storeProductId,
        int $storeId,
        string $type,
        string $role,
        string $sourceModule,
        ?int $sourceId,
        string $title,
        ?string $publicUrl,
        string $mimeType,
        string $extension,
        bool $prestashop,
        bool $webCatalogue,
        float $score,
        int $sortOrder,
        array $metadata,
        $now
    ): void {
        DB::table('lsg_catalog_product_assets')->updateOrInsert(
            ['product_id' => $productId, 'asset_role' => $role, 'source_id' => $sourceId],
            [
                'store_product_id' => $storeProductId,
                'store_id' => $storeId,
                'asset_type' => $type,
                'source_module' => $sourceModule,
                'title' => $title,
                'file_path' => $publicUrl ? ltrim($publicUrl, '/') : null,
                'public_url' => $publicUrl,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'language' => 'pt',
                'is_public' => true,
                'is_primary' => $role === 'card_image' || $role === 'set_logo',
                'is_syncable_to_prestashop' => $prestashop,
                'is_syncable_to_webcatalogue' => $webCatalogue,
                'approval_status' => 'approved',
                'brand_compliance_status' => 'approved',
                'quality_score' => $score,
                'sort_order' => $sortOrder,
                'metadata' => json_encode($metadata),
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    private function replaceLogs(int $productId, object $card, array $ad, $now): void
    {
        DB::table('lsg_catalog_logs')
            ->where('loggable_type', 'product_growth_mtg_card')
            ->where('loggable_id', $productId)
            ->delete();

        DB::table('lsg_catalog_logs')->insert([
            'loggable_type' => 'product_growth_mtg_card',
            'loggable_id' => $productId,
            'event' => 'ai_ads_manager',
            'severity' => 'info',
            'title' => 'Anuncio individual criado',
            'message' => $ad['primary_text'],
            'payload' => json_encode($ad),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('lsg_catalog_logs')->insert([
            'loggable_type' => 'product_growth_mtg_card',
            'loggable_id' => $productId,
            'event' => 'prestashop_bridge',
            'severity' => 'info',
            'title' => 'Produto marcado para sincronizacao',
            'message' => "Carta {$card->name} pronta para create/update no PrestaShop.",
            'payload' => json_encode([
                'sync_status' => 'ready_to_sync',
                'source_mtg_card_id' => $card->id,
            ]),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function workflowSteps(string $setName, int $cardCount): array
    {
        return [
            'product_core' => [
                'title' => 'Product Core',
                'owner' => 'Catalogo',
                'status' => 'completed',
                'content' => "Dados MTG locais importados para {$setName}: SKU, referencia, preco, raridade, tipo e imagem.",
                'output' => "{$cardCount} cartas prontas para workflow.",
            ],
            'store_brand_manager' => [
                'title' => 'Store Brand Manager',
                'owner' => 'Stores',
                'status' => 'completed',
                'content' => 'Produtos associados a TCG Collectors, Magic The Gathering e supplier Wizards.',
                'output' => 'Canal, marca e supplier definidos.',
            ],
            'marketing_content_manager' => [
                'title' => 'Marketing Content Manager',
                'owner' => 'Marketing',
                'status' => 'completed',
                'content' => 'Copy base e SEO criados a partir dos dados da carta e do set.',
                'output' => 'Conteudo comercial pronto.',
            ],
            'creative_asset_manager' => [
                'title' => 'Creative Asset Manager',
                'owner' => 'Creative',
                'status' => 'approved',
                'content' => 'Imagem local da carta ligada como asset principal e criativo de anuncio.',
                'output' => 'Assets aprovados.',
            ],
            'ai_ads_manager' => [
                'title' => 'AI Ads Manager',
                'owner' => 'Ads',
                'status' => 'ready',
                'content' => 'Anuncio individual criado para cada carta do set.',
                'output' => 'Anuncios prontos para campanha Mirrodin.',
            ],
            'publisher_export_manager' => [
                'title' => 'Publisher & Export Manager',
                'owner' => 'Publicacao',
                'status' => 'ready',
                'content' => 'Produtos preparados para listagem individual e export.',
                'output' => 'Export pronto.',
            ],
            'prestashop_bridge' => [
                'title' => 'PrestaShop 9 Bridge',
                'owner' => 'Integracoes',
                'status' => 'ready_to_sync',
                'content' => 'Produtos e assets marcados para sincronizacao PrestaShop.',
                'output' => 'Payloads em ready_to_sync.',
            ],
        ];
    }

    private function adPayload(object $card, string $setName, string $reference): array
    {
        $rarity = $card->rarity ? Str::title((string) $card->rarity) : 'MTG';
        $type = $card->card_type ?: 'Magic The Gathering card';

        return [
            'campaign' => 'mtg-mirrodin',
            'reference' => $reference,
            'headline' => Str::limit("{$card->name} - {$setName}", 60, ''),
            'primary_text' => Str::limit("Adiciona {$card->name} de {$setName} a tua colecao Magic The Gathering.", 125, ''),
            'description' => Str::limit("{$rarity} | {$type}", 90, ''),
            'cta' => 'Ver carta',
            'format' => 'single_product_card',
            'audience' => 'Colecionadores Magic The Gathering',
        ];
    }

    private function cardDescription(object $card, string $setName): string
    {
        $lines = [
            "{$card->name} do set {$setName} para Magic The Gathering.",
            $card->card_type ? "Tipo: {$card->card_type}" : null,
            $card->rarity ? 'Raridade: ' . Str::title((string) $card->rarity) : null,
            $card->mana_cost ? "Mana cost: {$card->mana_cost}" : null,
            $card->power || $card->toughness ? "Power/Toughness: {$card->power}/{$card->toughness}" : null,
            $card->oracle_text ? "Oracle text: {$card->oracle_text}" : null,
            $card->flavor_text ? "Flavor text: {$card->flavor_text}" : null,
            $card->artist ? "Artist: {$card->artist}" : null,
        ];

        return implode("\n", array_filter($lines));
    }

    private function collectorReference(string $collector): string
    {
        if (ctype_digit($collector)) {
            return str_pad($collector, 3, '0', STR_PAD_LEFT);
        }

        return strtoupper(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $collector), '-'));
    }

    private function collectorSortValue(string $collector): string
    {
        if (preg_match('/^(\d+)(.*)$/', $collector, $matches)) {
            return str_pad($matches[1], 8, '0', STR_PAD_LEFT) . $matches[2];
        }

        return $collector;
    }
}
