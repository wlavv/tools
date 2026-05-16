<?php

namespace Modules\WebCatalogue\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Product;
use Modules\WebCatalogue\Models\ProductPrice;
use Modules\WebCatalogue\Models\Resource;
use Modules\WebCatalogue\Models\Store;

class SeedTcgCollectorsMirrodinCommand extends Command
{
    protected $signature = 'webcatalogue:seed-tcg-collectors-mirrodin {--refresh-images : Download card images again even when the local file exists}';

    protected $description = 'Create the TCG-Collectors store, MTG Mirrodin catalogue, sealed products and all Mirrodin cards.';

    private const USER_AGENT = 'WebCatalogueSeeder/1.0 (tcg-collectors import)';

    public function handle(): int
    {
        $store = Store::updateOrCreate(
            ['slug' => 'tcg-collectors'],
            [
                'name' => 'TCG-Collectors',
                'code' => 'TCG-COLLECTORS',
                'domain' => 'tcg-collectors.com',
                'status' => 'active',
                'metadata' => [
                    'front' => [
                        'intro_text' => 'Scan trading cards and TCG products to access card details, set information, images and collector resources.',
                        'layout' => 'visual_gallery',
                        'image_fit' => 'contain',
                    ],
                    'source' => 'webcatalogue_seed',
                ],
            ]
        );

        $catalogue = Catalogue::updateOrCreate(
            ['id_store' => $store->id, 'slug' => 'mtg-mirrodin'],
            [
                'name' => 'MTG - Mirrodin',
                'description' => 'Magic: The Gathering Mirrodin set catalogue for object scan and collector intelligence tests.',
                'catalogue_type' => 'tcg_set',
                'show_prices' => true,
                'price_mode' => 'reference',
                'visibility' => 'public',
                'status' => 'active',
                'published_at' => now(),
                'metadata' => [
                    'game' => 'Magic: The Gathering',
                    'set_code' => 'MRD',
                    'released_at' => '2003-10-02',
                    'source' => 'Scryfall',
                ],
            ]
        );

        $this->info('Store and catalogue ready.');

        $sealedCount = $this->seedSealedProducts($store, $catalogue);
        $cards = $this->fetchMirrodinCards();

        $createdCards = 0;
        $updatedImages = 0;
        $bar = $this->output->createProgressBar(count($cards));
        $bar->start();

        foreach ($cards as $card) {
            $product = $this->upsertCardProduct($store, $catalogue, $card);
            if ($this->syncCardImage($store, $product, $card)) {
                $updatedImages++;
            }
            $createdCards++;
            $bar->advance();
            usleep(75000);
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('TCG-Collectors Mirrodin import complete.');
        $this->line('Sealed/default products: ' . $sealedCount);
        $this->line('Cards processed: ' . $createdCards);
        $this->line('Images downloaded/updated: ' . $updatedImages);
        $this->line('Store: ' . route('webcatalogue.stores.show', $store));
        $this->line('Catalogue front: ' . route('webcatalogue.front.catalogue.show', [$store->slug, $catalogue->slug]));

        return self::SUCCESS;
    }

    private function seedSealedProducts(Store $store, Catalogue $catalogue): int
    {
        $products = [
            ['reference' => 'MRD-FULL-SET', 'name' => 'Mirrodin Full Set', 'category' => 'Complete set', 'price' => null],
            ['reference' => 'MRD-BOOSTER-BOX', 'name' => 'Mirrodin Booster Box', 'category' => 'Sealed product', 'price' => null],
            ['reference' => 'MRD-BOOSTER-PACK', 'name' => 'Mirrodin Booster Pack', 'category' => 'Sealed product', 'price' => null],
            ['reference' => 'MRD-DECK-BAIT-BLUDGEON', 'name' => 'Mirrodin Theme Deck - Bait & Bludgeon', 'category' => 'Theme deck', 'price' => null],
            ['reference' => 'MRD-DECK-LITTLE-BASHERS', 'name' => 'Mirrodin Theme Deck - Little Bashers', 'category' => 'Theme deck', 'price' => null],
            ['reference' => 'MRD-DECK-SACRIFICIAL-BAM', 'name' => 'Mirrodin Theme Deck - Sacrificial Bam', 'category' => 'Theme deck', 'price' => null],
            ['reference' => 'MRD-DECK-WICKED-BIG', 'name' => 'Mirrodin Theme Deck - Wicked Big', 'category' => 'Theme deck', 'price' => null],
        ];

        foreach ($products as $index => $data) {
            $product = Product::updateOrCreate(
                ['id_store' => $store->id, 'reference' => $data['reference']],
                [
                    'external_source' => 'webcatalogue_seed',
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'short_description' => 'Mirrodin sealed/default catalogue product for scan and product intelligence validation.',
                    'description' => 'Collector product associated with Magic: The Gathering Mirrodin.',
                    'brand' => 'Magic: The Gathering',
                    'category' => $data['category'],
                    'price' => $data['price'],
                    'currency' => 'EUR',
                    'status' => 'active',
                    'metadata' => [
                        'game' => 'Magic: The Gathering',
                        'set_code' => 'MRD',
                        'product_kind' => Str::slug($data['category'], '_'),
                    ],
                ]
            );

            $this->attachToCatalogue($product, $catalogue, $index);
        }

        return count($products);
    }

    private function fetchMirrodinCards(): array
    {
        $cards = [];
        $url = 'https://api.scryfall.com/cards/search?order=set&q=e%3Amrd&unique=prints';

        while ($url) {
            $response = Http::withHeaders($this->headers())
                ->timeout(60)
                ->get($url)
                ->throw()
                ->json();

            foreach (($response['data'] ?? []) as $card) {
                $cards[] = $card;
            }

            $url = !empty($response['has_more']) ? ($response['next_page'] ?? null) : null;
            usleep(100000);
        }

        return $cards;
    }

    private function upsertCardProduct(Store $store, Catalogue $catalogue, array $card): Product
    {
        $collector = (string) ($card['collector_number'] ?? $card['id']);
        $reference = 'MRD-' . strtoupper(str_pad($collector, 3, '0', STR_PAD_LEFT));
        $name = (string) ($card['name'] ?? $reference);

        $product = Product::updateOrCreate(
            ['id_store' => $store->id, 'reference' => $reference],
            [
                'external_id' => $card['id'] ?? null,
                'external_source' => 'scryfall',
                'sku' => $card['arena_id'] ?? null,
                'ean13' => null,
                'name' => $name,
                'slug' => Str::slug('mrd-' . $collector . '-' . $name),
                'short_description' => $this->shortCardDescription($card),
                'description' => $this->cardDescription($card),
                'brand' => 'Magic: The Gathering',
                'category' => $card['type_line'] ?? 'Trading card',
                'price' => $this->decimalPrice($card['prices']['eur'] ?? $card['prices']['usd'] ?? null),
                'currency' => !empty($card['prices']['eur']) ? 'EUR' : 'USD',
                'status' => 'active',
                'metadata' => [
                    'game' => 'Magic: The Gathering',
                    'set_code' => strtoupper((string) ($card['set'] ?? 'mrd')),
                    'collector_number' => $collector,
                    'rarity' => $card['rarity'] ?? null,
                    'mana_cost' => $card['mana_cost'] ?? null,
                    'colors' => $card['colors'] ?? [],
                    'color_identity' => $card['color_identity'] ?? [],
                    'type_line' => $card['type_line'] ?? null,
                    'oracle_text' => $card['oracle_text'] ?? null,
                    'artist' => $card['artist'] ?? null,
                    'scryfall_uri' => $card['scryfall_uri'] ?? null,
                    'image_status' => $card['image_status'] ?? null,
                ],
            ]
        );

        $this->attachToCatalogue($product, $catalogue, (int) preg_replace('/\D+/', '', $collector));
        $this->syncReferencePrice($store, $product, $card);
        $this->syncExternalResource($store, $product, $card);

        return $product;
    }

    private function syncCardImage(Store $store, Product $product, array $card): bool
    {
        $imageUrl = $card['image_uris']['normal'] ?? $card['image_uris']['large'] ?? null;
        if (!$imageUrl && !empty($card['card_faces'][0]['image_uris']['normal'])) {
            $imageUrl = $card['card_faces'][0]['image_uris']['normal'];
        }

        if (!$imageUrl) {
            return false;
        }

        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';
        $path = 'webcatalogue/stores/' . $store->id . '/products/' . $product->id . '/images/' . Str::slug($product->reference) . '.' . $extension;

        $downloaded = false;
        if ($this->option('refresh-images') || !Storage::disk('public')->exists($path)) {
            $binary = Http::withHeaders($this->headers())
                ->timeout(60)
                ->get($imageUrl)
                ->throw()
                ->body();
            Storage::disk('public')->put($path, $binary);
            $downloaded = true;
        }

        Resource::updateOrCreate(
            [
                'id_store' => $store->id,
                'id_product' => $product->id,
                'resource_type' => 'image',
                'source_url' => $imageUrl,
            ],
            [
                'resource_owner_type' => 'product',
                'resource_owner_id' => $product->id,
                'title' => $product->reference . ' - card image',
                'description' => 'Card image imported from Scryfall for scan/fingerprint matching.',
                'source_type' => 'external_import',
                'file_path' => $path,
                'public_url' => '/storage/' . ltrim($path, '/'),
                'filename' => basename($path),
                'mime_type' => 'image/jpeg',
                'file_size' => Storage::disk('public')->exists($path) ? Storage::disk('public')->size($path) : null,
                'extension' => $extension,
                'is_main' => true,
                'sort_order' => 0,
                'status' => 'active',
                'metadata' => [
                    'source' => 'Scryfall',
                    'image_uri' => $imageUrl,
                    'card_id' => $card['id'] ?? null,
                ],
            ]
        );

        return $downloaded;
    }

    private function syncReferencePrice(Store $store, Product $product, array $card): void
    {
        $price = $this->decimalPrice($card['prices']['eur'] ?? $card['prices']['usd'] ?? null);
        if ($price === null) {
            return;
        }

        ProductPrice::updateOrCreate(
            [
                'id_store' => $store->id,
                'id_product' => $product->id,
                'price_type' => 'reference',
                'currency' => !empty($card['prices']['eur']) ? 'EUR' : 'USD',
            ],
            [
                'regular_price' => $price,
                'sale_price' => null,
                'tax_included' => true,
                'status' => 'active',
                'metadata' => [
                    'source' => 'Scryfall prices',
                ],
            ]
        );
    }

    private function syncExternalResource(Store $store, Product $product, array $card): void
    {
        if (empty($card['scryfall_uri'])) {
            return;
        }

        Resource::updateOrCreate(
            [
                'id_store' => $store->id,
                'id_product' => $product->id,
                'resource_type' => 'external_link',
                'source_url' => $card['scryfall_uri'],
            ],
            [
                'resource_owner_type' => 'product',
                'resource_owner_id' => $product->id,
                'title' => 'Scryfall card reference',
                'description' => 'External card reference and rulings page.',
                'source_type' => 'external_link',
                'is_main' => false,
                'sort_order' => 90,
                'status' => 'active',
                'metadata' => [
                    'source' => 'Scryfall',
                    'card_id' => $card['id'] ?? null,
                ],
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

    private function shortCardDescription(array $card): string
    {
        $parts = array_filter([
            $card['type_line'] ?? null,
            !empty($card['rarity']) ? ucfirst((string) $card['rarity']) : null,
            !empty($card['collector_number']) ? '#' . $card['collector_number'] : null,
        ]);

        return implode(' - ', $parts) ?: 'Magic: The Gathering card from Mirrodin.';
    }

    private function cardDescription(array $card): string
    {
        $lines = [];
        if (!empty($card['oracle_text'])) {
            $lines[] = $card['oracle_text'];
        }
        if (!empty($card['flavor_text'])) {
            $lines[] = $card['flavor_text'];
        }
        if (!empty($card['artist'])) {
            $lines[] = 'Artist: ' . $card['artist'];
        }

        return implode("\n\n", $lines) ?: $this->shortCardDescription($card);
    }

    private function decimalPrice($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 4);
    }

    private function headers(): array
    {
        return [
            'User-Agent' => self::USER_AGENT,
            'Accept' => 'application/json',
        ];
    }
}
