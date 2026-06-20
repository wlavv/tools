<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\Mtg\Models\TcgMtgAbility;

class TcgMtgAbilitiesSeeder extends Seeder
{
    private const SOURCE = 'MTG Comprehensive Rules / Internal Commercial Taxonomy';

    public function run(): void
    {
        if (!Schema::hasTable('tcg_mtg_abilities')) {
            return;
        }

        $keywordAbilities = [
            'Flying', 'Reach', 'Trample', 'Haste', 'Vigilance', 'Lifelink', 'Deathtouch',
            'First Strike', 'Double Strike', 'Menace', 'Defender', 'Flash', 'Hexproof',
            'Indestructible', 'Ward', 'Prowess', 'Protection', 'Shroud', 'Fear', 'Intimidate',
            'Infect', 'Wither', 'Affinity', 'Afterlife', 'Annihilator', 'Ascend', 'Awaken',
            'Backup', 'Bestow', 'Blitz', 'Bloodthirst', 'Boast', 'Buyback', 'Cascade',
            'Casualty', 'Champion', 'Changeling', 'Cipher', 'Cleave', 'Companion', 'Conspire',
            'Convoke', 'Crew', 'Cycling', 'Dash', 'Daybound', 'Delve', 'Disguise', 'Disturb',
            'Dredge', 'Echo', 'Embalm', 'Emerge', 'Encore', 'Enlist', 'Entwine', 'Epic',
            'Equip', 'Escalate', 'Escape', 'Eternalize', 'Evoke', 'Evolve', 'Exalted',
            'Exploit', 'Extort', 'Fabricate', 'Fading', 'Flashback', 'Forecast', 'Foretell',
            'Fortify', 'Fuse', 'Gift', 'Gravestorm', 'Haunt', 'Hideaway', 'Impending',
            'Improvise', 'Jump-Start', 'Kicker', 'Level Up', 'Living Weapon', 'Madness',
            'Melee', 'Mentor', 'Miracle', 'Modular', 'Morph', 'Mutate', 'Myriad', 'Nightbound',
            'Ninjutsu', 'Offering', 'Outlast', 'Overload', 'Partner', 'Persist', 'Phasing',
            'Plot', 'Poisonous', 'Prototype', 'Provoke', 'Prowl', 'Rebound', 'Recover',
            'Reconfigure', 'Renown', 'Replicate', 'Retrace', 'Riot', 'Scavenge', 'Shadow',
            'Skulk', 'Soulbond', 'Soulshift', 'Spectacle', 'Splice', 'Split Second', 'Storm',
            'Suspend', 'Totem Armor', 'Training', 'Transmute', 'Tribute', 'Undying', 'Unearth',
            'Vanishing',
        ];

        $keywordActions = [
            'Destroy', 'Exile', 'Counter', 'Discard', 'Sacrifice', 'Scry', 'Mill', 'Investigate',
            'Create', 'Search', 'Shuffle', 'Tap', 'Untap', 'Fight', 'Proliferate', 'Surveil',
            'Explore', 'Discover', 'Goad', 'Connive', 'Manifest', 'Transform', 'Populate',
            'Learn', 'Amass', 'Adapt', 'Venture into the Dungeon', 'The Ring Tempts You',
            'Attach', 'Cast', 'Activate', 'Regenerate', 'Reveal', 'Exchange', 'Double', 'Vote',
            'Bolster', 'Clash', 'Collect Evidence', 'Conjure', 'Detain', 'Fateseal', 'Incubate',
            'Meld', 'Monstrosity', 'Planeswalk', 'Support', 'Suspect', 'Solve',
        ];

        $abilityWords = [
            'Landfall', 'Magecraft', 'Metalcraft', 'Morbid', 'Raid', 'Revolt', 'Delirium',
            'Domain', 'Constellation', 'Heroic', 'Threshold', 'Hellbent', 'Ferocious',
            'Enrage', 'Coven', 'Alliance', 'Celebration', 'Eerie', 'Adamant', 'Addendum',
            'Battalion', 'Bloodrush', 'Channel', 'Chroma', 'Cohort', 'Converge',
            "Council's Dilemma", 'Fateful Hour', 'Formidable', 'Grandeur', 'Imprint',
            'Inspired', 'Join Forces', 'Kinship', 'Lieutenant', 'Pack Tactics', 'Parley',
            'Rally', 'Spell Mastery', 'Strive', 'Sweep', 'Tempting Offer', 'Undergrowth',
            'Will of the Council',
        ];

        $commercialTags = [
            'Removal', 'Board Wipe', 'Counterspell', 'Card Draw', 'Ramp', 'Mana Fixing',
            'Tutor', 'Token Generator', 'Sacrifice Outlet', 'Aristocrats', 'Reanimator',
            'Graveyard Synergy', 'Blink', 'Bounce', 'Burn', 'Lifegain', 'Mill', 'Discard',
            'Stax', 'Voltron', 'Equipment', 'Auras', 'Enchantress', 'Artifacts Matter',
            'Spellslinger', 'Combat Tricks', 'Protection', 'Pump', 'Counters', 'Poison',
            'Infect Strategy', 'Extra Turns', 'Extra Combat', 'Landfall Strategy',
            'Tribal Support', 'Commander Staple', 'Combo Piece', 'Finisher', 'Value Engine',
            'Aggro', 'Control', 'Midrange', 'Combo',
        ];

        $filterableNames = [
            'Flying', 'Reach', 'Trample', 'Haste', 'Vigilance', 'Lifelink', 'Deathtouch',
            'First Strike', 'Double Strike', 'Menace', 'Defender', 'Flash', 'Hexproof',
            'Indestructible', 'Ward', 'Prowess', 'Protection', 'Shroud', 'Fear', 'Intimidate',
            'Infect', 'Wither', 'Landfall', 'Magecraft', 'Metalcraft', 'Morbid', 'Raid',
            'Revolt', 'Delirium', 'Domain', 'Constellation', 'Heroic', 'Threshold',
            'Cycling', 'Kicker', 'Flashback', 'Madness', 'Ninjutsu', 'Storm', 'Cascade',
            'Convoke', 'Delve', 'Escape', 'Unearth', 'Persist', 'Undying',
        ];

        $evergreenNames = [
            'Flying', 'Reach', 'Trample', 'Haste', 'Vigilance', 'Lifelink', 'Deathtouch',
            'First Strike', 'Double Strike', 'Menace', 'Defender', 'Flash', 'Hexproof',
            'Indestructible', 'Ward', 'Prowess', 'Protection',
        ];

        $commercialNames = array_merge($filterableNames, $commercialTags);

        $groups = [
            'keyword_ability' => $keywordAbilities,
            'keyword_action' => $keywordActions,
            'ability_word' => $abilityWords,
            'commercial_tag' => $commercialTags,
        ];

        foreach ($groups as $type => $names) {
            foreach (array_values(array_unique($names)) as $index => $name) {
                $slug = Str::slug($name);
                $record = $this->buildAbilityRecord($name, $type, $filterableNames, $commercialNames, $evergreenNames, $index + 1);
                $existing = TcgMtgAbility::query()->where('slug', $slug)->first();

                if ($existing) {
                    $record['type'] = $existing->type;
                    $record['is_official'] = (bool) $existing->is_official || (bool) $record['is_official'];
                    $record['is_evergreen'] = (bool) $existing->is_evergreen || (bool) $record['is_evergreen'];
                    $record['is_filterable'] = (bool) $existing->is_filterable || (bool) $record['is_filterable'];
                    $record['is_commercial_tag'] = (bool) $existing->is_commercial_tag || (bool) $record['is_commercial_tag'];
                    $record['sort_order'] = min((int) $existing->sort_order, (int) $record['sort_order']);
                }

                TcgMtgAbility::query()->updateOrCreate(['slug' => $slug], $record);
            }
        }
    }

    private function buildAbilityRecord(string $name, string $type, array $filterableNames, array $commercialNames, array $evergreenNames, int $sortOrder): array
    {
        $isCommercialTag = $type === 'commercial_tag';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => $type,
            'is_official' => !$isCommercialTag,
            'is_evergreen' => in_array($name, $evergreenNames, true),
            'is_filterable' => $isCommercialTag || in_array($name, $filterableNames, true),
            'is_commercial_tag' => $isCommercialTag || in_array($name, $commercialNames, true),
            'description' => null,
            'source' => self::SOURCE,
            'sort_order' => $sortOrder,
            'active' => true,
        ];
    }
}
