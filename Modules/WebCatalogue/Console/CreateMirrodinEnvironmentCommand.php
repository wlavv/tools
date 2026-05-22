<?php

namespace Modules\WebCatalogue\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Modules\WebCatalogue\Models\Catalogue;
use Modules\WebCatalogue\Models\Store;
use Modules\WebCatalogue\Models\StoreEnvironment;

class CreateMirrodinEnvironmentCommand extends Command
{
    protected $signature = 'webcatalogue:create-mirrodin-environment {--store=tcg-collectors} {--catalogue=mirrodin}';

    protected $description = 'Create or update a thematic immersive Mirrodin environment for the TCG catalogue.';

    public function handle(): int
    {
        $storeSlug = (string) $this->option('store');
        $catalogueSlug = (string) $this->option('catalogue');

        $store = Store::query()
            ->where('slug', $storeSlug)
            ->orWhere('code', $storeSlug)
            ->first();

        if (!$store) {
            $this->error("Store not found: {$storeSlug}");
            return self::FAILURE;
        }

        $catalogue = Catalogue::query()
            ->where('id_store', $store->id)
            ->where(function ($query) use ($catalogueSlug) {
                $query->where('slug', $catalogueSlug)
                    ->orWhere('name', 'like', '%' . str_replace('-', ' ', $catalogueSlug) . '%');
            })
            ->first();

        $metadata = [
            'theme' => 'mirrodin',
            'tone' => 'ancient_artifact_world',
            'visual_direction' => [
                'materials' => ['dark oxidized metal', 'brass highlights', 'etched stone', 'blue arcane glow'],
                'palette' => ['#0b1018', '#1f2933', '#7a6330', '#c7a755', '#62b6ff'],
                'texture_notes' => 'Use layered metal panels, faint machinery grooves, dust, worn edges and subtle mana-like light pulses.',
            ],
            'audio_direction' => [
                'ambient_bed' => 'low metallic resonance, distant machinery, soft wind through hollow structures',
                'interaction_sounds' => ['soft metal click', 'low arcane shimmer', 'subtle rotating mechanism'],
                'target_lufs' => -22,
            ],
            'immersive_goals' => [
                'make card viewing feel like inspecting artifacts inside a metal plane',
                'keep contrast high enough for card readability',
                'avoid overpowering motion while scanning or browsing',
            ],
            'procedural_scene' => [
                'floor' => 'brushed dark metal grid',
                'backdrop' => 'distant copper structures and blue energy lines',
                'particles' => 'slow dust motes and tiny sparks',
            ],
        ];

        $environment = StoreEnvironment::updateOrCreate(
            [
                'id_store' => $store->id,
                'slug' => 'mirrodin-artifact-vault',
            ],
            [
                'id_catalogue' => $catalogue?->id,
                'name' => 'Mirrodin Artifact Vault',
                'is_default' => false,
                'environment_type' => 'vr',
                'background_type' => 'procedural',
                'background_color' => '#0b1018',
                'lighting_preset' => 'mirrodin_cold_forge',
                'camera_preset' => 'artifact_inspection',
                'vr_scene_config' => [
                    'scene' => 'mirrodin_artifact_vault',
                    'fog' => ['color' => '#0b1018', 'density' => 0.035],
                    'lighting' => [
                        'key' => ['color' => '#c7a755', 'intensity' => 1.25],
                        'rim' => ['color' => '#62b6ff', 'intensity' => 1.6],
                        'ambient' => ['color' => '#1f2933', 'intensity' => 0.85],
                    ],
                    'floor' => ['type' => 'procedural_grid', 'color' => '#151a20', 'accent' => '#7a6330'],
                    'audio' => ['enabled' => true, 'profile' => 'mirrodin_low_metallic_ambience'],
                ],
                'ar_scene_config' => [
                    'placement' => 'tabletop',
                    'scale' => 0.22,
                    'shadow' => true,
                    'environment_audio' => false,
                ],
                'metadata' => $metadata,
                'status' => 'active',
            ]
        );

        $scope = $catalogue ? "catalogue {$catalogue->name}" : 'store-wide fallback';
        $this->info("Environment ready: #{$environment->id} {$environment->name} ({$scope})");

        return self::SUCCESS;
    }
}
