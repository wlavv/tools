<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CatalogManagerMtgColorIdentitySymbolsSeeder extends Seeder
{
    public function run(): void
    {
        $characteristicId = DB::table('lsg_catalog_core_characteristics')
            ->where('slug', 'color_identity')
            ->value('id');

        if (!$characteristicId) {
            return;
        }

        $singleSymbols = [
            'w' => ['/images/mtg/custom_images/W.svg', 'White mana symbol'],
            'u' => ['/images/mtg/custom_images/U.svg', 'Blue mana symbol'],
            'b' => ['/images/mtg/custom_images/B.svg', 'Black mana symbol'],
            'r' => ['/images/mtg/custom_images/R.svg', 'Red mana symbol'],
            'g' => ['/images/mtg/custom_images/G.svg', 'Green mana symbol'],
            'c' => ['/images/mtg/custom_images/C.svg', 'Colorless mana symbol'],
        ];

        foreach ($singleSymbols as $value => [$url, $alt]) {
            DB::table('lsg_catalog_core_characteristic_values')
                ->where('characteristic_id', $characteristicId)
                ->where('value', $value)
                ->update([
                    'image_url' => $url,
                    'image_alt' => $alt,
                    'updated_at' => now(),
                ]);
        }

        $directory = public_path('images/mtg/catalog/color_identity');
        File::ensureDirectoryExists($directory);

        $symbolByLetter = [
            'w' => public_path('images/mtg/custom_images/W.svg'),
            'u' => public_path('images/mtg/custom_images/U.svg'),
            'b' => public_path('images/mtg/custom_images/B.svg'),
            'r' => public_path('images/mtg/custom_images/R.svg'),
            'g' => public_path('images/mtg/custom_images/G.svg'),
            'c' => public_path('images/mtg/custom_images/C.svg'),
        ];

        DB::table('lsg_catalog_core_characteristic_values')
            ->where('characteristic_id', $characteristicId)
            ->whereRaw('CHAR_LENGTH(value) > 1')
            ->orderBy('position')
            ->get(['id', 'value', 'label'])
            ->each(function ($row) use ($directory, $symbolByLetter): void {
                $value = Str::lower((string) $row->value);
                $letters = str_split($value);
                $symbolSize = 48;
                $gap = 6;
                $padding = 8;
                $width = max(64, ($symbolSize * count($letters)) + ($gap * max(0, count($letters) - 1)) + ($padding * 2));
                $x = $padding;

                $images = collect($letters)
                    ->map(function (string $letter) use (&$x, $symbolByLetter, $symbolSize, $gap): string {
                        $path = $symbolByLetter[$letter] ?? null;
                        if (!$path || !File::exists($path)) {
                            return '';
                        }

                        $dataUri = 'data:image/svg+xml;base64,' . base64_encode(File::get($path));
                        $markup = '<image href="' . $dataUri . '" x="' . $x . '" y="8" width="' . $symbolSize . '" height="' . $symbolSize . '"/>';
                        $x += $symbolSize + $gap;

                        return $markup;
                    })
                    ->implode('');

                $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $width . ' 64" role="img" aria-label="' . e($row->label) . ' color identity">'
                    . '<rect width="' . $width . '" height="64" fill="none"/>'
                    . $images
                    . '</svg>';

                File::put($directory . DIRECTORY_SEPARATOR . $value . '.svg', $svg);

                DB::table('lsg_catalog_core_characteristic_values')
                    ->where('id', $row->id)
                    ->update([
                        'image_url' => '/images/mtg/catalog/color_identity/' . $value . '.svg',
                        'image_alt' => $row->label . ' color identity',
                        'updated_at' => now(),
                    ]);
            });
    }
}
