<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;
use Modules\IdeaLab\Models\IdeaCategory;

return new class extends Migration
{
    public function up(): void
    {
        $categories = [
            ['name' => 'B.O. Module', 'icon' => 'fa-solid fa-cubes', 'color' => '#0d6efd'],
            ['name' => 'SaaS / Platform', 'icon' => 'fa-solid fa-cloud', 'color' => '#198754'],
            ['name' => 'AI Tool', 'icon' => 'fa-solid fa-brain', 'color' => '#6f42c1'],
            ['name' => 'E-commerce', 'icon' => 'fa-solid fa-cart-shopping', 'color' => '#fd7e14'],
            ['name' => 'Internal Operations', 'icon' => 'fa-solid fa-gears', 'color' => '#20c997'],
            ['name' => 'Automation', 'icon' => 'fa-solid fa-wand-magic-sparkles', 'color' => '#0dcaf0'],
            ['name' => 'Data / Reporting', 'icon' => 'fa-solid fa-chart-line', 'color' => '#6610f2'],
            ['name' => 'Customer Workflow', 'icon' => 'fa-solid fa-users-gear', 'color' => '#dc3545'],
        ];

        foreach ($categories as $index => $category) {
            IdeaCategory::query()->firstOrCreate(
                ['slug' => Str::slug($category['name'])],
                array_merge($category, [
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ])
            );
        }
    }

    public function down(): void
    {
        // Keep categories because production ideas may already reference them.
    }
};
