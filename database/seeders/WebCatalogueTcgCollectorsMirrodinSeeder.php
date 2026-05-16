<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class WebCatalogueTcgCollectorsMirrodinSeeder extends Seeder
{
    public function run(): void
    {
        $refreshImages = filter_var(
            env('WEBCATALOGUE_TCG_REFRESH_IMAGES', false),
            FILTER_VALIDATE_BOOL
        );

        $exitCode = Artisan::call('webcatalogue:seed-tcg-collectors-mirrodin', [
            '--refresh-images' => $refreshImages,
        ]);

        if ($this->command) {
            $this->command->line(Artisan::output());
        }

        if ($exitCode !== 0) {
            throw new \RuntimeException('TCG-Collectors Mirrodin seed failed with exit code ' . $exitCode . '.');
        }
    }
}
