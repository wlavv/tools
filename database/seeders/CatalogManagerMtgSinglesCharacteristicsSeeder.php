<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CatalogManagerMtgSinglesCharacteristicsSeeder extends Seeder
{
    public function run(): void
    {
        if (
            !Schema::hasTable('lsg_catalog_core_characteristics')
            || !Schema::hasTable('lsg_catalog_core_characteristic_values')
            || !Schema::hasTable('lsg_catalog_category_characteristics')
            || !Schema::hasTable('catalog_store_categories')
            || !Schema::hasTable('catalog_store_category_lang')
        ) {
            return;
        }

        $characteristics = [
            'rarity' => ['Rarity', 'select', 'Identity', ['Common', 'Uncommon', 'Rare', 'Mythic Rare', 'Special', 'Bonus']],
            'condition' => ['Condition', 'select', 'Identity', ['Mint', 'Near Mint', 'Excellent', 'Good', 'Light Played', 'Played', 'Poor']],
            'language' => ['Language', 'select', 'Identity', ['English', 'Portuguese', 'Spanish', 'French', 'German', 'Italian', 'Japanese', 'Korean', 'Russian', 'Simplified Chinese', 'Traditional Chinese']],
            'finish' => ['Finish', 'select', 'Identity', ['Non-Foil', 'Traditional Foil', 'Etched Foil', 'Glossy']],
            'version_treatment' => ['Version / Treatment', 'select', 'Identity', ['Regular', 'Extended Art', 'Borderless', 'Showcase', 'Retro Frame', 'Full Art', 'Textured Foil', 'Surge Foil', 'Galaxy Foil', 'Confetti Foil', 'Serialized', 'Promo', 'Prerelease Promo', 'Buy-a-Box Promo', 'Bundle Promo', 'Store Championship Promo']],
            'mana_cost' => ['Mana Cost', 'text', 'Mana', []],
            'mana_value' => ['Mana Value', 'number', 'Mana', []],
            'colors' => ['Colors', 'select', 'Mana', ['White', 'Blue', 'Black', 'Red', 'Green', 'Colorless', 'Multicolor']],
            'color_identity' => ['Color Identity', 'select', 'Mana', ['W', 'U', 'B', 'R', 'G', 'C', 'WU', 'WB', 'WR', 'WG', 'UB', 'UR', 'UG', 'BR', 'BG', 'RG', 'WUB', 'WUR', 'WUG', 'WBR', 'WBG', 'WRG', 'UBR', 'UBG', 'URG', 'BRG', 'WUBR', 'WUBG', 'WURG', 'WBRG', 'UBRG', 'WUBRG']],
            'type_line' => ['Type Line', 'text', 'Type', []],
            'supertypes' => ['Supertypes', 'select', 'Type', ['Basic', 'Legendary', 'Ongoing', 'Snow', 'World']],
            'card_types' => ['Card Types', 'select', 'Type', ['Artifact', 'Battle', 'Conspiracy', 'Creature', 'Dungeon', 'Enchantment', 'Instant', 'Kindred', 'Land', 'Phenomenon', 'Plane', 'Planeswalker', 'Scheme', 'Sorcery', 'Vanguard']],
            'subtypes' => ['Subtypes', 'select', 'Type', ['Advisor', 'Aetherborn', 'Angel', 'Artificer', 'Assassin', 'Aura', 'Avatar', 'Beast', 'Bird', 'Cat', 'Cleric', 'Construct', 'Demon', 'Dragon', 'Druid', 'Elemental', 'Elf', 'Equipment', 'Faerie', 'Fish', 'Goblin', 'Golem', 'Human', 'Illusion', 'Instant', 'Knight', 'Merfolk', 'Myr', 'Ogre', 'Phyrexian', 'Rogue', 'Samurai', 'Soldier', 'Spirit', 'Thopter', 'Vampire', 'Vedalken', 'Warrior', 'Wizard', 'Zombie', 'Forest', 'Island', 'Mountain', 'Plains', 'Swamp', 'Urza']],
            'power' => ['Power', 'text', 'Stats', []],
            'toughness' => ['Toughness', 'text', 'Stats', []],
            'loyalty' => ['Loyalty', 'text', 'Stats', []],
            'defense' => ['Defense', 'text', 'Stats', []],
            'keywords' => ['Keywords', 'select', 'Rules', ['Deathtouch', 'Defender', 'Double strike', 'Enchant', 'Equip', 'First strike', 'Flash', 'Flying', 'Haste', 'Hexproof', 'Indestructible', 'Lifelink', 'Menace', 'Protection', 'Reach', 'Trample', 'Vigilance', 'Ward', 'Affinity', 'Buyback', 'Cascade', 'Convoke', 'Cycling', 'Flashback', 'Kicker', 'Madness', 'Miracle', 'Morph', 'Prowess', 'Scry', 'Storm', 'Suspend']],
            'abilities' => ['Abilities', 'select', 'Rules', []],
            'oracle_text' => ['Oracle Text', 'text', 'Rules', []],
        ];

        $now = now();
        $characteristicIds = [];

        $hasUsageScope = Schema::hasColumn('lsg_catalog_core_characteristics', 'usage_scope');
        $combinationCharacteristicSlugs = ['condition', 'language', 'finish', 'version_treatment'];

        foreach ($characteristics as $slug => [$name, $dataType, $section, $values]) {
            $payload = [
                'name' => $name,
                'data_type' => $dataType,
                'unit' => null,
                'is_filterable' => in_array($slug, ['colors', 'color_identity', 'supertypes', 'card_types', 'subtypes', 'rarity', 'condition', 'language', 'finish', 'version_treatment', 'keywords', 'abilities'], true),
                'is_searchable' => true,
                'is_seo_keyword' => in_array($slug, ['colors', 'color_identity', 'type_line', 'supertypes', 'card_types', 'subtypes', 'rarity', 'condition', 'language', 'finish', 'version_treatment', 'keywords', 'abilities'], true),
                'is_syncable' => true,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ];

            if ($hasUsageScope) {
                $payload['usage_scope'] = in_array($slug, $combinationCharacteristicSlugs, true) ? 'combination' : 'product';
            }

            $id = DB::table('lsg_catalog_core_characteristics')->updateOrInsert(
                ['slug' => $slug],
                $payload
            );

            $characteristicId = (int) DB::table('lsg_catalog_core_characteristics')->where('slug', $slug)->value('id');
            $characteristicIds[$slug] = $characteristicId;

            DB::table('lsg_catalog_core_characteristic_values')
                ->where('characteristic_id', $characteristicId)
                ->delete();

            foreach (array_values($this->valuesForCharacteristic($slug, $values)) as $position => $label) {
                DB::table('lsg_catalog_core_characteristic_values')->insert([
                    'characteristic_id' => $characteristicId,
                    'value' => Str::slug($label, '_'),
                    'label' => $label,
                    'position' => $position + 1,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $categoryId = $this->findMtgSinglesCategoryId();

        if (!$categoryId) {
            return;
        }

        DB::table('lsg_catalog_category_characteristics')
            ->where('store_category_id', $categoryId)
            ->whereIn('characteristic_id', array_values($characteristicIds))
            ->delete();

        $rows = collect(array_keys($characteristics))
            ->map(fn (string $slug, int $index) => [
                'store_category_id' => $categoryId,
                'characteristic_id' => $characteristicIds[$slug],
                'is_required' => in_array($characteristicIds[$slug], [
                    $characteristicIds['mana_cost'],
                    $characteristicIds['mana_value'],
                    $characteristicIds['colors'],
                    $characteristicIds['color_identity'],
                    $characteristicIds['type_line'],
                    $characteristicIds['card_types'],
                    $characteristicIds['rarity'],
                ], true),
                'position' => $index + 1,
                'section' => $characteristics[$slug][2],
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        DB::table('lsg_catalog_category_characteristics')->insert($rows);

        $this->seedCombinationAttributes($categoryId, $now);
    }

    private function seedCombinationAttributes(int $categoryId, $now): void
    {
        if (
            !Schema::hasTable('catalog_combination_attributes')
            || !Schema::hasTable('catalog_combination_attribute_values')
            || !Schema::hasTable('catalog_category_combination_attributes')
        ) {
            return;
        }

        $attributes = [
            'condition' => ['Condition', ['Mint', 'Near Mint', 'Excellent', 'Good', 'Light Played', 'Played', 'Poor']],
            'language' => ['Language', ['English', 'Portuguese', 'Spanish', 'French', 'German', 'Italian', 'Japanese', 'Korean', 'Russian', 'Simplified Chinese', 'Traditional Chinese']],
            'finish' => ['Finish', ['Non-Foil', 'Traditional Foil', 'Etched Foil', 'Glossy']],
            'version_treatment' => ['Version / Treatment', ['Regular', 'Extended Art', 'Borderless', 'Showcase', 'Retro Frame', 'Full Art', 'Textured Foil', 'Surge Foil', 'Galaxy Foil', 'Confetti Foil', 'Serialized', 'Promo', 'Prerelease Promo', 'Buy-a-Box Promo', 'Bundle Promo', 'Store Championship Promo']],
        ];

        $attributeIds = [];

        foreach ($attributes as $slug => [$name, $values]) {
            DB::table('catalog_combination_attributes')->updateOrInsert(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'display_type' => 'select',
                    'is_required' => true,
                    'affects_price' => true,
                    'affects_stock' => true,
                    'is_active' => true,
                    'position' => count($attributeIds) + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $attributeId = (int) DB::table('catalog_combination_attributes')->where('slug', $slug)->value('id');
            $attributeIds[$slug] = $attributeId;

            DB::table('catalog_combination_attribute_values')->where('attribute_id', $attributeId)->delete();

            foreach ($values as $position => $label) {
                DB::table('catalog_combination_attribute_values')->insert([
                    'attribute_id' => $attributeId,
                    'value' => Str::slug($label, '_'),
                    'label' => $label,
                    'position' => $position + 1,
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        DB::table('catalog_category_combination_attributes')
            ->where('store_category_id', $categoryId)
            ->whereIn('attribute_id', array_values($attributeIds))
            ->delete();

        $rows = collect(array_values($attributeIds))
            ->map(fn (int $attributeId, int $index) => [
                'store_category_id' => $categoryId,
                'attribute_id' => $attributeId,
                'is_required' => true,
                'position' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($rows) {
            DB::table('catalog_category_combination_attributes')->insert($rows);
        }
    }

    private function valuesForCharacteristic(string $slug, array $values): array
    {
        if ($slug !== 'abilities' || !Schema::hasTable('tcg_mtg_abilities')) {
            return $values;
        }

        return DB::table('tcg_mtg_abilities')
            ->where('active', true)
            ->where('is_official', true)
            ->whereIn('type', ['keyword_ability', 'keyword_action', 'ability_word'])
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    private function findMtgSinglesCategoryId(): ?int
    {
        $categories = DB::table('catalog_store_categories as c')
            ->leftJoin('catalog_store_category_lang as cl', 'cl.store_category_id', '=', 'c.id')
            ->leftJoin('catalog_stores as s', 's.id', '=', 'c.store_id')
            ->select('c.id', 'c.parent_id', 'c.code', 'cl.name', 's.name as store_name', 's.code as store_code')
            ->get();

        $byId = $categories->keyBy('id');

        $score = function ($category) use ($byId): int {
            $parts = [
                $category->name,
                $category->code,
                $category->store_name,
                $category->store_code,
            ];

            $cursor = $category;
            while ($cursor && $cursor->parent_id) {
                $cursor = $byId->get($cursor->parent_id);
                if ($cursor) {
                    $parts[] = $cursor->name;
                    $parts[] = $cursor->code;
                }
            }

            $text = Str::lower(implode(' ', array_filter($parts)));
            $score = 0;

            foreach (['tcg', 'collectors', 'magic', 'gathering', 'mtg'] as $needle) {
                $score += str_contains($text, $needle) ? 10 : 0;
            }

            foreach (['single', 'singles', 'carta', 'cartas'] as $needle) {
                $score += str_contains($text, $needle) ? 25 : 0;
            }

            return $score;
        };

        $match = $categories
            ->sortByDesc($score)
            ->first(fn ($category) => $score($category) >= 35);

        return $match ? (int) $match->id : null;
    }
}
