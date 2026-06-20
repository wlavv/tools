<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CatalogManagerMtgMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['catalog_core_manufacturers', 'catalog_core_suppliers'] as $table) {
            if (!Schema::hasTable($table)) {
                $this->command?->warn("Tabela em falta: {$table}");
                return;
            }
        }

        $now = now();

        DB::transaction(function () use ($now): void {
            DB::table('catalog_core_manufacturers')
                ->where(function ($query): void {
                    $query->where('slug', 'like', '%demo%')
                        ->orWhere('name', 'like', '%Demo%');
                })
                ->delete();

            DB::table('catalog_core_suppliers')
                ->where(function ($query): void {
                    $query->where('code', 'like', '%DEMO%')
                        ->orWhere('name', 'like', '%Demo%');
                })
                ->delete();

            DB::table('catalog_core_manufacturers')->updateOrInsert(
                ['slug' => 'magic-the-gathering'],
                [
                    'name' => 'Magic The Gathering',
                    'website' => 'https://magic.wizards.com',
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

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
        });

        $this->command?->info('Catalog Manager MTG master data atualizado: Magic The Gathering + Wizards.');
    }
}
