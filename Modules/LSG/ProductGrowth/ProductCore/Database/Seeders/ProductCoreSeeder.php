<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\LSG\ProductGrowth\ProductCore\Models\ProductStore;

class ProductCoreSeeder extends Seeder
{
    public function run(): void
    {
        $stores = [
            ['Life-style.pt', 'life-style.pt', 'life-style.pt'],
            ['As-Yourself', 'as-yourself.pt', 'as-yourself.pt'],
            ['World Decor', 'world-decor.pt', 'world-decor.pt'],
            ['Casual Vibe', 'casual-vibe.com', 'casual-vibe.com'],
            ['2Play4Fun', '2play4fun.com', '2play4fun.com'],
            ['TCG Collectors', 'tcg-collectors.com', 'tcg-collectors.com'],
        ];

        foreach ($stores as [$name, $slug, $domain]) {
            ProductStore::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'site_type' => 'store',
                    'domain' => $domain,
                    'public_url' => 'https://' . $domain,
                    'environment' => 'production',
                    'status' => 'active',
                    'default_language' => 'pt',
                    'default_currency' => 'EUR',
                ]
            );
        }
    }
}
