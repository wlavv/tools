<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogManagerMaterializeCharacteristicImagesSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('lsg_catalog_core_characteristic_values')) {
            return;
        }

        DB::table('lsg_catalog_core_characteristic_values')
            ->whereNotNull('image_url')
            ->orderBy('characteristic_id')
            ->orderBy('position')
            ->get(['id', 'characteristic_id', 'value', 'image_url'])
            ->each(function ($row): void {
                $imageUrl = (string) $row->image_url;

                if ($imageUrl === '') {
                    return;
                }

                if (Str::startsWith($imageUrl, ['/storage/', 'storage/'])) {
                    return;
                }

                if (Str::contains($imageUrl, '/storage/')) {
                    DB::table('lsg_catalog_core_characteristic_values')
                        ->where('id', $row->id)
                        ->update([
                            'image_url' => '/storage/' . Str::after($imageUrl, '/storage/'),
                            'updated_at' => now(),
                        ]);

                    return;
                }

                if (!Str::startsWith($imageUrl, '/images/')) {
                    return;
                }

                $sourcePath = public_path(ltrim($imageUrl, '/'));
                if (!File::exists($sourcePath) || !File::isFile($sourcePath)) {
                    return;
                }

                $extension = File::extension($sourcePath) ?: 'svg';
                $filename = Str::slug((string) $row->value) . '.' . $extension;
                $targetPath = 'catalog-manager/characteristics/' . $row->characteristic_id . '/' . $filename;

                Storage::disk('public')->put($targetPath, File::get($sourcePath));

                DB::table('lsg_catalog_core_characteristic_values')
                    ->where('id', $row->id)
                    ->update([
                        'image_url' => '/storage/' . ltrim($targetPath, '/'),
                        'updated_at' => now(),
                    ]);
            });
    }
}
