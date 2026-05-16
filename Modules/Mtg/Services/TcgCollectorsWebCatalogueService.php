<?php

namespace Modules\Mtg\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Modules\Mtg\Models\mtg_cards;
use Modules\Mtg\Models\mtg_sets;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Services\Imports\WebCataloguePayloadImportService;

class TcgCollectorsWebCatalogueService
{
    private const USER_AGENT = 'MtgModuleTCGCollectors/1.0 (WebCatalogue import)';

    public function __construct(private WebCataloguePayloadImportService $importer) {}

    public function listSets(bool $refresh = false): array
    {
        if ($refresh) {
            Cache::forget('mtg.tcg_collectors.scryfall_sets');
        }

        return Cache::remember('mtg.tcg_collectors.scryfall_sets', now()->addHours(12), function () {
            $response = Http::withHeaders($this->headers())
                ->timeout(60)
                ->get('https://api.scryfall.com/sets')
                ->throw()
                ->json();

            return collect($response['data'] ?? [])
                ->filter(fn ($set) => ($set['set_type'] ?? '') !== 'memorabilia')
                ->map(fn ($set) => [
                    'code' => strtolower((string) ($set['code'] ?? '')),
                    'name' => (string) ($set['name'] ?? ''),
                    'set_type' => (string) ($set['set_type'] ?? ''),
                    'card_count' => (int) ($set['card_count'] ?? 0),
                    'released_at' => $set['released_at'] ?? null,
                    'scryfall_uri' => $set['scryfall_uri'] ?? null,
                    'icon_svg_uri' => $set['icon_svg_uri'] ?? null,
                ])
                ->filter(fn ($set) => $set['code'] !== '' && $set['name'] !== '')
                ->values()
                ->all();
        });
    }

    public function importedSetCodes(): array
    {
        $store = Store::where('slug', 'tcg-collectors')->first();

        if (!$store) {
            return [];
        }

        return Catalogue::where('id_store', $store->id)
            ->where('catalogue_type', 'tcg_set')
            ->get()
            ->mapWithKeys(function (Catalogue $catalogue) {
                $metadata = is_array($catalogue->metadata) ? $catalogue->metadata : [];
                $code = strtolower((string) ($metadata['set_code'] ?? Str::after((string) $catalogue->slug, 'mtg-')));

                return [$code => [
                    'catalogue_id' => $catalogue->id,
                    'name' => $catalogue->name,
                    'products_count' => $catalogue->products()->count(),
                    'updated_at' => optional($catalogue->updated_at)->toDateTimeString(),
                ]];
            })
            ->all();
    }

    public function importSet(string $setCode, bool $refreshImages = false): array
    {
        $set = $this->findSet(strtolower(trim($setCode)));
        $cards = $this->fetchCards($set['code']);
        $payload = $this->toWebCataloguePayload($set, $cards, $refreshImages);

        return $this->importer->import($payload) + [
            'set' => $set,
            'cards_processed' => count($cards),
        ];
    }

    public function importLocalSet(mtg_sets $set, array $options = []): array
    {
        $setCode = (string) $set->sub_set_code;

        if (!($options['skip_card_sync'] ?? false)) {
            mtg_cards::updateCardsFromSet((int) $set->id, $setCode);
        }

        $cards = mtg_cards::getCardsBySet($setCode);
        $payload = $this->localSetToWebCataloguePayload($set, $cards, $options);

        return $this->importer->import($payload) + [
            'set' => [
                'code' => strtolower($setCode),
                'name' => (string) $set->set_name,
            ],
            'cards_processed' => $cards->count(),
        ];
    }

    private function findSet(string $setCode): array
    {
        foreach ($this->listSets() as $set) {
            if ($set['code'] === $setCode) {
                return $set;
            }
        }

        $response = Http::withHeaders($this->headers())
            ->timeout(60)
            ->get('https://api.scryfall.com/sets/' . urlencode($setCode))
            ->throw()
            ->json();

        return [
            'code' => strtolower((string) ($response['code'] ?? $setCode)),
            'name' => (string) ($response['name'] ?? strtoupper($setCode)),
            'set_type' => (string) ($response['set_type'] ?? ''),
            'card_count' => (int) ($response['card_count'] ?? 0),
            'released_at' => $response['released_at'] ?? null,
            'scryfall_uri' => $response['scryfall_uri'] ?? null,
            'icon_svg_uri' => $response['icon_svg_uri'] ?? null,
        ];
    }

    private function fetchCards(string $setCode): array
    {
        $cards = [];
        $url = 'https://api.scryfall.com/cards/search?order=set&q=e%3A' . urlencode($setCode) . '&unique=prints';

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

    private function toWebCataloguePayload(array $set, array $cards, bool $refreshImages): array
    {
        $setCode = strtoupper($set['code']);
        $products = $this->sealedProducts($set);

        foreach ($cards as $card) {
            $collector = (string) ($card['collector_number'] ?? $card['id']);
            $reference = $setCode . '-' . strtoupper(str_pad($collector, 3, '0', STR_PAD_LEFT));
            $imageUrl = $card['image_uris']['normal'] ?? $card['image_uris']['large'] ?? ($card['card_faces'][0]['image_uris']['normal'] ?? null);
            $price = $this->decimalPrice($card['prices']['eur'] ?? $card['prices']['usd'] ?? null);
            $currency = !empty($card['prices']['eur']) ? 'EUR' : 'USD';

            $resources = [];
            if ($imageUrl) {
                $resources[] = [
                    'resource_type' => 'image',
                    'source_url' => $imageUrl,
                    'download' => true,
                    'refresh' => $refreshImages,
                    'title' => $reference . ' - card image',
                    'description' => 'Card image imported from Scryfall for scan/fingerprint matching.',
                    'mime_type' => 'image/jpeg',
                    'is_main' => true,
                    'headers' => $this->headers(),
                    'metadata' => ['source' => 'Scryfall', 'card_id' => $card['id'] ?? null],
                ];
            }
            if (!empty($card['scryfall_uri'])) {
                $resources[] = [
                    'resource_type' => 'external_link',
                    'source_url' => $card['scryfall_uri'],
                    'title' => 'Scryfall card reference',
                    'description' => 'External card reference and rulings page.',
                    'source_type' => 'external_link',
                    'sort_order' => 90,
                    'metadata' => ['source' => 'Scryfall', 'card_id' => $card['id'] ?? null],
                ];
            }

            $products[] = [
                'reference' => $reference,
                'external_id' => $card['id'] ?? null,
                'external_source' => 'scryfall',
                'sku' => $card['arena_id'] ?? null,
                'name' => (string) ($card['name'] ?? $reference),
                'slug' => Str::slug(strtolower($setCode) . '-' . $collector . '-' . ($card['name'] ?? $reference)),
                'short_description' => $this->shortCardDescription($card),
                'description' => $this->cardDescription($card),
                'brand' => 'Magic: The Gathering',
                'category' => $card['type_line'] ?? 'Trading card',
                'price' => $price,
                'currency' => $currency,
                'status' => 'active',
                'sort_order' => (int) preg_replace('/\D+/', '', $collector),
                'metadata' => [
                    'game' => 'Magic: The Gathering',
                    'set_code' => $setCode,
                    'set_name' => $set['name'],
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
                'prices' => $price === null ? [] : [[
                    'price_type' => 'reference',
                    'currency' => $currency,
                    'regular_price' => $price,
                    'tax_included' => true,
                    'status' => 'active',
                    'metadata' => ['source' => 'Scryfall prices'],
                ]],
                'resources' => $resources,
            ];
        }

        return [
            'store' => [
                'slug' => 'tcg-collectors',
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
                    'source' => 'mtg_module',
                ],
            ],
            'catalogue' => [
                'slug' => 'mtg-' . strtolower($set['code']),
                'name' => 'MTG - ' . $set['name'],
                'description' => 'Magic: The Gathering ' . $set['name'] . ' set catalogue for scan and collector intelligence.',
                'catalogue_type' => 'tcg_set',
                'show_prices' => true,
                'price_mode' => 'reference',
                'visibility' => 'public',
                'status' => 'active',
                'published_at' => now(),
                'metadata' => [
                    'game' => 'Magic: The Gathering',
                    'set_code' => $setCode,
                    'set_name' => $set['name'],
                    'set_type' => $set['set_type'] ?? null,
                    'card_count' => $set['card_count'] ?? null,
                    'released_at' => $set['released_at'] ?? null,
                    'scryfall_uri' => $set['scryfall_uri'] ?? null,
                    'icon_svg_uri' => $set['icon_svg_uri'] ?? null,
                    'source' => 'Scryfall',
                ],
            ],
            'products' => $products,
        ];
    }

    private function sealedProducts(array $set): array
    {
        $setCode = strtoupper($set['code']);
        $setName = $set['name'];
        $items = [
            ['suffix' => 'FULL-SET', 'name' => $setName . ' Full Set', 'category' => 'Complete set'],
            ['suffix' => 'BOOSTER-BOX', 'name' => $setName . ' Booster Box', 'category' => 'Sealed product'],
            ['suffix' => 'BOOSTER-PACK', 'name' => $setName . ' Booster Pack', 'category' => 'Sealed product'],
        ];

        if (strtolower($set['code']) === 'mrd') {
            foreach (['Bait & Bludgeon', 'Little Bashers', 'Sacrificial Bam', 'Wicked Big'] as $deck) {
                $items[] = ['suffix' => 'DECK-' . strtoupper(Str::slug($deck, '-')), 'name' => $setName . ' Theme Deck - ' . $deck, 'category' => 'Theme deck'];
            }
        }

        return collect($items)->map(fn ($item, $index) => [
            'reference' => $setCode . '-' . $item['suffix'],
            'external_source' => 'mtg_module',
            'name' => $item['name'],
            'slug' => Str::slug($item['name']),
            'short_description' => $setName . ' sealed/default catalogue product for scan and product intelligence validation.',
            'description' => 'Collector product associated with Magic: The Gathering ' . $setName . '.',
            'brand' => 'Magic: The Gathering',
            'category' => $item['category'],
            'currency' => 'EUR',
            'status' => 'active',
            'sort_order' => $index,
            'metadata' => ['game' => 'Magic: The Gathering', 'set_code' => $setCode, 'product_kind' => Str::slug($item['category'], '_')],
        ])->all();
    }

    private function shortCardDescription(array $card): string
    {
        return implode(' - ', array_filter([
            $card['type_line'] ?? null,
            !empty($card['rarity']) ? ucfirst((string) $card['rarity']) : null,
            !empty($card['collector_number']) ? '#' . $card['collector_number'] : null,
        ])) ?: 'Magic: The Gathering card.';
    }

    private function cardDescription(array $card): string
    {
        return implode("\n\n", array_filter([
            $card['oracle_text'] ?? null,
            $card['flavor_text'] ?? null,
            !empty($card['artist']) ? 'Artist: ' . $card['artist'] : null,
        ])) ?: $this->shortCardDescription($card);
    }

    private function decimalPrice($value): ?float
    {
        return ($value === null || $value === '') ? null : round((float) $value, 4);
    }

    private function headers(): array
    {
        return ['User-Agent' => self::USER_AGENT, 'Accept' => 'application/json'];
    }

    private function localSetToWebCataloguePayload(mtg_sets $set, $cards, array $options): array
    {
        $setCode = strtoupper((string) $set->sub_set_code);
        $setName = (string) $set->set_name;
        $storeSlug = Str::slug($options['store_slug'] ?? 'tcg-collectors');
        $catalogueName = trim((string) ($options['catalogue_name'] ?? 'MTG - ' . $setName));
        $catalogueSlug = trim((string) ($options['catalogue_slug'] ?? Str::slug('mtg-' . strtolower($setCode))));
        $products = [];

        if ($options['include_sealed_products'] ?? true) {
            $products = $this->sealedProducts([
                'code' => strtolower($setCode),
                'name' => $setName,
            ]);
        }

        foreach ($cards as $card) {
            $collector = (string) $card->collector_number;
            $reference = $setCode . '-' . strtoupper(str_pad($collector, 3, '0', STR_PAD_LEFT));
            $resources = [];

            if (!empty($card->image_url)) {
                $localPublicPath = str_starts_with((string) $card->image_url, '/images/')
                    ? ltrim((string) $card->image_url, '/')
                    : null;

                $resources[] = [
                    'resource_type' => 'image',
                    'source_url' => $card->image_url,
                    'public_url' => $card->image_url,
                    'local_public_path' => $localPublicPath,
                    'download' => $localPublicPath === null,
                    'title' => $reference . ' - card image',
                    'description' => 'Card image imported from MTG module for scan/fingerprint matching.',
                    'mime_type' => 'image/jpeg',
                    'is_main' => true,
                    'metadata' => [
                        'source' => 'MTG module',
                        'mtg_card_id' => $card->id,
                    ],
                ];
            }

            if (!empty($card->scryfall_uri)) {
                $resources[] = [
                    'resource_type' => 'external_link',
                    'source_url' => $card->scryfall_uri,
                    'title' => 'Scryfall card reference',
                    'description' => 'External card reference and rulings page.',
                    'source_type' => 'external_link',
                    'sort_order' => 90,
                    'metadata' => [
                        'source' => 'Scryfall',
                        'mtg_card_id' => $card->id,
                    ],
                ];
            }

            $price = $this->decimalPrice($card->price ?? null);

            $products[] = [
                'reference' => $reference,
                'external_id' => $card->external_id,
                'external_source' => 'mtg_module',
                'name' => (string) $card->name,
                'slug' => Str::slug(strtolower($setCode) . '-' . $collector . '-' . $card->name),
                'short_description' => implode(' - ', array_filter([
                    $card->card_type,
                    $card->rarity ? ucfirst((string) $card->rarity) : null,
                    '#' . $collector,
                ])),
                'description' => implode("\n\n", array_filter([
                    $card->oracle_text,
                    $card->flavor_text,
                    $card->artist ? 'Artist: ' . $card->artist : null,
                ])),
                'brand' => 'Magic: The Gathering',
                'category' => $card->card_type ?: 'Trading card',
                'price' => $price,
                'currency' => 'EUR',
                'status' => 'active',
                'sort_order' => (int) preg_replace('/\D+/', '', $collector),
                'metadata' => [
                    'game' => 'Magic: The Gathering',
                    'set_code' => $setCode,
                    'set_name' => $setName,
                    'collector_number' => $collector,
                    'rarity' => $card->rarity,
                    'mana_cost' => $card->mana_cost,
                    'power' => $card->power,
                    'toughness' => $card->toughness,
                    'color_group' => $card->color_group,
                    'artist' => $card->artist,
                    'scryfall_uri' => $card->scryfall_uri,
                    'mtg_card_id' => $card->id,
                ],
                'prices' => $price === null ? [] : [[
                    'price_type' => 'reference',
                    'currency' => 'EUR',
                    'regular_price' => $price,
                    'tax_included' => true,
                    'status' => 'active',
                    'metadata' => ['source' => 'MTG module'],
                ]],
                'resources' => $resources,
            ];
        }

        return [
            'store' => [
                'slug' => $storeSlug,
                'name' => trim((string) ($options['store_name'] ?? 'TCG-Collectors')),
                'code' => trim((string) ($options['store_code'] ?? 'TCG-COLLECTORS')),
                'domain' => trim((string) ($options['store_domain'] ?? 'tcg-collectors.com')) ?: null,
                'status' => 'active',
                'metadata' => [
                    'front' => [
                        'intro_text' => 'Scan trading cards and TCG products to access card details, set information, images and collector resources.',
                        'layout' => 'visual_gallery',
                        'image_fit' => 'contain',
                    ],
                    'source' => 'mtg_module_show_set',
                ],
            ],
            'catalogue' => [
                'slug' => $catalogueSlug,
                'name' => $catalogueName,
                'description' => trim((string) ($options['catalogue_description'] ?? 'Magic: The Gathering ' . $setName . ' set catalogue for scan and collector intelligence.')),
                'catalogue_type' => 'tcg_set',
                'show_prices' => true,
                'price_mode' => 'reference',
                'visibility' => 'public',
                'status' => 'active',
                'published_at' => now(),
                'metadata' => [
                    'game' => 'Magic: The Gathering',
                    'set_code' => $setCode,
                    'set_name' => $setName,
                    'set_type' => $set->set_type,
                    'card_count' => $set->card_count,
                    'released_at' => $set->released_at,
                    'scryfall_uri' => $set->scryfall_uri,
                    'icon_svg_uri' => $set->icon_svg_uri,
                    'source' => 'MTG module',
                ],
            ],
            'products' => $products,
        ];
    }
}
