<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TcgCollectorsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $catalogStore = $this->catalogStore();

        if (!$catalogStore) {
            $this->command?->warn('TCG Collectors store not found in catalog_stores.');
            return;
        }

        foreach ($this->tree() as $index => $node) {
            $this->seedNode($node, null, $index + 1, $catalogStore->id, $now);
        }

        $this->command?->info('TCG Collectors category tree seeded.');
    }

    private function seedNode(array $node, ?int $catalogParentId, int $position, int $catalogStoreId, $now): void
    {
        $code = $node['code'];
        $name = $node['name'];
        $description = $node['description'] ?? $this->descriptionFor($name);

        $catalogId = $this->upsertCatalogCategory($catalogStoreId, $catalogParentId, $code, $name, $description, $position, $now);

        foreach (($node['children'] ?? []) as $childIndex => $child) {
            $this->seedNode($child, $catalogId, $childIndex + 1, $catalogStoreId, $now);
        }
    }

    private function upsertCatalogCategory(int $storeId, ?int $parentId, string $code, string $name, string $description, int $position, $now): int
    {
        if (!Schema::hasTable('catalog_store_categories') || !Schema::hasTable('catalog_store_category_lang')) {
            return 0;
        }

        DB::table('catalog_store_categories')->updateOrInsert(
            ['store_id' => $storeId, 'code' => $code],
            [
                'parent_id' => $parentId,
                'active' => true,
                'position' => $position,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        $categoryId = (int) DB::table('catalog_store_categories')
            ->where('store_id', $storeId)
            ->where('code', $code)
            ->value('id');

        DB::table('catalog_store_category_lang')->updateOrInsert(
            ['store_category_id' => $categoryId, 'locale' => 'pt'],
            [
                'name' => $name,
                'description' => $description,
                'link_rewrite' => Str::slug($name),
                'meta_title' => $name,
                'meta_description' => $description,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        return $categoryId;
    }

    private function catalogStore(): ?object
    {
        if (!Schema::hasTable('catalog_stores')) {
            return null;
        }

        return DB::table('catalog_stores')
            ->where('code', 'tcg_collectors')
            ->orWhere('code', 'tcg-collectors')
            ->orWhere('domain', 'tcg-collectors.com')
            ->orWhere('name', 'TCG Collectors')
            ->first();
    }

    private function descriptionFor(string $name): string
    {
        return "Categoria TCG Collectors para {$name}.";
    }

    private function tree(): array
    {
        return [
            [
                'code' => 'tcg',
                'name' => 'TCG',
                'description' => 'Jogos de cartas colecionaveis, singles, produtos selados e colecionaveis.',
                'children' => [
                    $this->game('magic-the-gathering', 'Magic: The Gathering', [
                        ['mtg-singles', 'Singles'],
                        ['mtg-sealed-products', 'Sealed Products', [
                            ['mtg-booster-packs', 'Booster Packs'],
                            ['mtg-booster-boxes', 'Booster Boxes'],
                            ['mtg-collector-boosters', 'Collector Boosters'],
                            ['mtg-commander-decks', 'Commander Decks'],
                            ['mtg-bundles', 'Bundles'],
                            ['mtg-starter-kits', 'Starter Kits'],
                            ['mtg-prerelease-kits', 'Pre-release Kits'],
                        ]],
                        ['mtg-sets', 'Sets'],
                        ['mtg-graded-cards', 'Graded Cards'],
                        ['mtg-lots-collections', 'Lots / Collections'],
                    ]),
                    $this->game('pokemon', 'Pokemon', [
                        ['pokemon-singles', 'Singles'],
                        ['pokemon-sealed-products', 'Sealed Products', [
                            ['pokemon-booster-packs', 'Booster Packs'],
                            ['pokemon-booster-boxes', 'Booster Boxes'],
                            ['pokemon-elite-trainer-boxes', 'Elite Trainer Boxes'],
                            ['pokemon-tins', 'Tins'],
                            ['pokemon-collections', 'Collections'],
                            ['pokemon-battle-decks', 'Battle Decks'],
                        ]],
                        ['pokemon-sets', 'Sets'],
                        ['pokemon-graded-cards', 'Graded Cards'],
                        ['pokemon-lots-collections', 'Lots / Collections'],
                    ]),
                    $this->game('yu-gi-oh', 'Yu-Gi-Oh!', [
                        ['yugioh-singles', 'Singles'],
                        ['yugioh-sealed-products', 'Sealed Products', [
                            ['yugioh-booster-packs', 'Booster Packs'],
                            ['yugioh-booster-boxes', 'Booster Boxes'],
                            ['yugioh-structure-decks', 'Structure Decks'],
                            ['yugioh-starter-decks', 'Starter Decks'],
                            ['yugioh-tins', 'Tins'],
                        ]],
                        ['yugioh-sets', 'Sets'],
                        ['yugioh-graded-cards', 'Graded Cards'],
                        ['yugioh-lots-collections', 'Lots / Collections'],
                    ]),
                    $this->game('one-piece-card-game', 'One Piece Card Game', [
                        ['one-piece-singles', 'Singles'],
                        ['one-piece-sealed-products', 'Sealed Products', [
                            ['one-piece-booster-packs', 'Booster Packs'],
                            ['one-piece-booster-boxes', 'Booster Boxes'],
                            ['one-piece-starter-decks', 'Starter Decks'],
                            ['one-piece-double-packs', 'Double Packs'],
                        ]],
                        ['one-piece-sets', 'Sets'],
                        ['one-piece-graded-cards', 'Graded Cards'],
                        ['one-piece-lots-collections', 'Lots / Collections'],
                    ]),
                    $this->game('lorcana', 'Lorcana', [
                        ['lorcana-singles', 'Singles'],
                        ['lorcana-sealed-products', 'Sealed Products', [
                            ['lorcana-booster-packs', 'Booster Packs'],
                            ['lorcana-booster-boxes', 'Booster Boxes'],
                            ['lorcana-starter-decks', 'Starter Decks'],
                            ['lorcana-gift-sets', 'Gift Sets'],
                            ['lorcana-troves', 'Illumineer Troves'],
                        ]],
                        ['lorcana-sets', 'Sets'],
                        ['lorcana-graded-cards', 'Graded Cards'],
                        ['lorcana-lots-collections', 'Lots / Collections'],
                    ]),
                ],
            ],
            [
                'code' => 'accessories',
                'name' => 'Accessories',
                'description' => 'Acessorios para jogadores e colecionadores de TCG.',
                'children' => [
                    ['code' => 'sleeves', 'name' => 'Sleeves'],
                    ['code' => 'deck-boxes', 'name' => 'Deck Boxes'],
                    ['code' => 'playmats', 'name' => 'Playmats'],
                    ['code' => 'binders', 'name' => 'Binders'],
                    ['code' => 'toploaders', 'name' => 'Toploaders'],
                    ['code' => 'grading-protection', 'name' => 'Grading / Protection'],
                    ['code' => 'storage', 'name' => 'Storage'],
                ],
            ],
            ['code' => 'deals-clearance', 'name' => 'Deals / Clearance'],
        ];
    }

    private function game(string $code, string $name, array $children): array
    {
        return [
            'code' => $code,
            'name' => $name,
            'children' => array_map(fn (array $item) => [
                'code' => $item[0],
                'name' => $item[1],
                'children' => array_map(fn (array $child) => [
                    'code' => $child[0],
                    'name' => $child[1],
                ], $item[2] ?? []),
            ], $children),
        ];
    }
}
