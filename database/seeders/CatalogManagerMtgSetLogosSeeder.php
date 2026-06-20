<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CatalogManagerMtgSetLogosSeeder extends Seeder
{
    public function run(): void
    {
        $characteristicId = DB::table('lsg_catalog_core_characteristics')
            ->where('slug', 'set')
            ->value('id');

        if (!$characteristicId) {
            return;
        }

        DB::table('lsg_catalog_core_characteristic_values')
            ->where('characteristic_id', $characteristicId)
            ->orderBy('position')
            ->get(['id', 'value', 'label'])
            ->each(function ($row): void {
                $code = $this->setCodeFromValue((string) $row->value, (string) $row->label);
                if (!$code) {
                    return;
                }

                $path = public_path('images/mtg/' . $code . '/logo/' . $code . '.svg');
                if (!File::exists($path)) {
                    return;
                }

                DB::table('lsg_catalog_core_characteristic_values')
                    ->where('id', $row->id)
                    ->update([
                        'image_url' => '/images/mtg/' . $code . '/logo/' . $code . '.svg',
                        'image_alt' => $row->label . ' set logo',
                        'updated_at' => now(),
                    ]);
            });
    }

    private function setCodeFromValue(string $value, string $label): ?string
    {
        if (preg_match('/^\(([A-Z0-9]+)\)/', $label, $matches)) {
            return Str::lower($matches[1]);
        }

        $prefix = Str::before($value, '_');

        return filled($prefix) ? Str::lower($prefix) : null;
    }
}
