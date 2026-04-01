<?php

namespace Modules\RoadmapManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoadmapManagerDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $groups = [
            ['name' => 'Innovation', 'slug' => 'innovation', 'description' => 'Innovation roadmap group', 'color' => '#8b5cf6', 'icon' => 'fa-lightbulb', 'status' => 'active'],
            ['name' => 'Infrastructure', 'slug' => 'infrastructure', 'description' => 'Infrastructure roadmap group', 'color' => '#3b82f6', 'icon' => 'fa-server', 'status' => 'active'],
            ['name' => 'Commerce', 'slug' => 'commerce', 'description' => 'Commerce roadmap group', 'color' => '#10b981', 'icon' => 'fa-shopping-cart', 'status' => 'active'],
            ['name' => 'AI & Automation', 'slug' => 'ai-automation', 'description' => 'AI and automation roadmap group', 'color' => '#f59e0b', 'icon' => 'fa-robot', 'status' => 'active'],
        ];

        foreach ($groups as $group) {
            DB::table('wt_roadmap_groups')->updateOrInsert(
                ['slug' => $group['slug']],
                array_merge($group, ['uuid' => (string) Str::uuid(), 'updated_at' => $now, 'created_at' => $now])
            );
        }
    }
}
