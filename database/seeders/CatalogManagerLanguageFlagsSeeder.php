<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class CatalogManagerLanguageFlagsSeeder extends Seeder
{
    public function run(): void
    {
        $characteristicId = DB::table('lsg_catalog_core_characteristics')
            ->where('slug', 'language')
            ->value('id');

        if (!$characteristicId) {
            return;
        }

        $directory = public_path('storage/catalog-manager/characteristics/' . $characteristicId);
        File::ensureDirectoryExists($directory);

        $languages = [
            'English' => ['en', 'United Kingdom flag', $this->flagUk()],
            'Portuguese' => ['pt', 'Portugal flag', $this->flagPortugal()],
            'Spanish' => ['es', 'Spain flag', $this->flagSpain()],
            'French' => ['fr', 'France flag', $this->flagFrance()],
            'German' => ['de', 'Germany flag', $this->flagGermany()],
            'Italian' => ['it', 'Italy flag', $this->flagItaly()],
            'Japanese' => ['ja', 'Japan flag', $this->flagJapan()],
            'Korean' => ['ko', 'South Korea flag', $this->flagKorea()],
            'Russian' => ['ru', 'Russia flag', $this->flagRussia()],
            'Simplified Chinese' => ['zh-cn', 'China flag', $this->flagChina()],
            'Traditional Chinese' => ['zh-tw', 'Taiwan flag', $this->flagTaiwan()],
        ];

        $position = 1;

        foreach ($languages as $label => [$code, $alt, $svg]) {
            $filename = 'language-' . Str::slug($code) . '.svg';
            File::put($directory . DIRECTORY_SEPARATOR . $filename, $svg);

            $existingId = DB::table('lsg_catalog_core_characteristic_values')
                ->where('characteristic_id', $characteristicId)
                ->where(function ($query) use ($label, $code): void {
                    $query->where('label', $label)
                        ->orWhere('value', $label)
                        ->orWhere('value', $code);
                })
                ->value('id');

            $payload = [
                'characteristic_id' => $characteristicId,
                'value' => $code,
                'label' => $label,
                'image_url' => '/storage/catalog-manager/characteristics/' . $characteristicId . '/' . $filename,
                'image_alt' => $alt,
                'position' => $position,
                'active' => true,
                'updated_at' => now(),
            ];

            if ($existingId) {
                DB::table('lsg_catalog_core_characteristic_values')
                    ->where('id', $existingId)
                    ->update($payload);
            } else {
                $payload['created_at'] = now();

                DB::table('lsg_catalog_core_characteristic_values')->insert($payload);
            }

            $position++;
        }
    }

    private function svg(string $body): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 96 64" role="img">'
            . '<rect width="96" height="64" rx="8" fill="#fff"/>'
            . $body
            . '<rect width="96" height="64" rx="8" fill="none" stroke="rgba(15,23,42,.22)" stroke-width="2"/>'
            . '</svg>';
    }

    private function flagUk(): string
    {
        return $this->svg(
            '<rect width="96" height="64" rx="8" fill="#012169"/>'
            . '<path d="M0 0 96 64M96 0 0 64" stroke="#fff" stroke-width="14"/>'
            . '<path d="M0 0 96 64M96 0 0 64" stroke="#C8102E" stroke-width="8"/>'
            . '<path d="M48 0v64M0 32h96" stroke="#fff" stroke-width="22"/>'
            . '<path d="M48 0v64M0 32h96" stroke="#C8102E" stroke-width="12"/>'
        );
    }

    private function flagPortugal(): string
    {
        return $this->svg(
            '<rect width="38" height="64" rx="8" fill="#006600"/>'
            . '<path d="M30 0h58a8 8 0 0 1 8 8v48a8 8 0 0 1-8 8H30z" fill="#FF0000"/>'
            . '<circle cx="38" cy="32" r="10" fill="#FFCC00" stroke="#fff" stroke-width="2"/>'
        );
    }

    private function flagSpain(): string
    {
        return $this->svg(
            '<rect width="96" height="64" rx="8" fill="#AA151B"/>'
            . '<rect y="16" width="96" height="32" fill="#F1BF00"/>'
            . '<rect x="24" y="24" width="10" height="16" rx="2" fill="#AA151B"/>'
        );
    }

    private function flagFrance(): string
    {
        return $this->svg('<rect width="32" height="64" rx="8" fill="#0055A4"/><rect x="32" width="32" height="64" fill="#fff"/><path d="M64 0h24a8 8 0 0 1 8 8v48a8 8 0 0 1-8 8H64z" fill="#EF4135"/>');
    }

    private function flagGermany(): string
    {
        return $this->svg('<rect width="96" height="22" rx="8" fill="#000"/><rect y="21" width="96" height="22" fill="#DD0000"/><path d="M0 42h96v14a8 8 0 0 1-8 8H8a8 8 0 0 1-8-8z" fill="#FFCE00"/>');
    }

    private function flagItaly(): string
    {
        return $this->svg('<rect width="32" height="64" rx="8" fill="#009246"/><rect x="32" width="32" height="64" fill="#fff"/><path d="M64 0h24a8 8 0 0 1 8 8v48a8 8 0 0 1-8 8H64z" fill="#CE2B37"/>');
    }

    private function flagJapan(): string
    {
        return $this->svg('<circle cx="48" cy="32" r="18" fill="#BC002D"/>');
    }

    private function flagKorea(): string
    {
        return $this->svg(
            '<path d="M48 16a16 16 0 1 1 0 32 8 8 0 1 0 0-16 8 8 0 1 1 0-16z" fill="#CD2E3A"/>'
            . '<path d="M48 16a16 16 0 1 0 0 32 8 8 0 1 1 0-16 8 8 0 1 0 0-16z" fill="#0047A0"/>'
            . '<g stroke="#111" stroke-width="3"><path d="M20 13l12 8M17 18l12 8M64 43l12 8M67 38l12 8M69 14l-12 8M74 20l-12 8M28 42l-12 8M33 48l-12 8"/></g>'
        );
    }

    private function flagRussia(): string
    {
        return $this->svg('<rect width="96" height="22" rx="8" fill="#fff"/><rect y="21" width="96" height="22" fill="#0039A6"/><path d="M0 42h96v14a8 8 0 0 1-8 8H8a8 8 0 0 1-8-8z" fill="#D52B1E"/>');
    }

    private function flagChina(): string
    {
        return $this->svg('<rect width="96" height="64" rx="8" fill="#DE2910"/><path d="M20 12l3 8h9l-7 5 3 8-8-5-8 5 3-8-7-5h9zM42 10l2 4 4 1-4 2v4l-3-3-4 1 2-4-2-4 4 1zM52 20l2 4 4 1-4 2v4l-3-3-4 1 2-4-2-4 4 1zM52 36l2 4 4 1-4 2v4l-3-3-4 1 2-4-2-4 4 1zM42 46l2 4 4 1-4 2v4l-3-3-4 1 2-4-2-4 4 1z" fill="#FFDE00"/>');
    }

    private function flagTaiwan(): string
    {
        return $this->svg('<rect width="96" height="64" rx="8" fill="#FE0000"/><rect width="48" height="34" rx="8" fill="#000095"/><circle cx="24" cy="17" r="9" fill="#fff"/><circle cx="24" cy="17" r="5" fill="#000095"/>');
    }
}
