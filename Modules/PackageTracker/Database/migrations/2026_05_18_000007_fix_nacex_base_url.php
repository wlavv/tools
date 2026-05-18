<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\PackageTracker\Services\Carriers\Integrators\NacexIntegrator;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('package_tracker_carriers')) {
            return;
        }

        $integrator = new NacexIntegrator();

        DB::table('package_tracker_carriers')
            ->where('code', 'nacex')
            ->where(function ($query) {
                $query->whereNull('api_base_url')
                    ->orWhere('api_base_url', '')
                    ->orWhere('api_base_url', 'https://api.nacex.com')
                    ->orWhere('api_base_url', 'http://api.nacex.com');
            })
            ->update([
                'driver' => $integrator->clientClass(),
                'api_base_url' => $integrator->defaultBaseUrl(),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
