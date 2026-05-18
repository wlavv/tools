<?php

namespace Modules\PackageTracker\Database\Seeders;

use Illuminate\Database\Seeder;

class PackageTrackerDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PackageTrackerCarrierSeeder::class);
    }
}
