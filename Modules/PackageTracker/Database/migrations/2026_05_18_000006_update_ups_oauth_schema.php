<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\PackageTracker\Services\Carriers\Integrators\UpsIntegrator;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('package_tracker_carriers')) {
            return;
        }

        $integrator = new UpsIntegrator();
        $carrier = DB::table('package_tracker_carriers')->where('code', 'ups')->first();

        if (! $carrier) {
            return;
        }

        $settings = json_decode((string) $carrier->settings, true) ?: [];
        $settings['_integrator'] = array_merge($settings['_integrator'] ?? [], [
            'code' => $integrator->code(),
            'capabilities' => $integrator->capabilities(),
            'credential_schema' => $integrator->credentialSchema(),
            'tracking_number_hints' => $integrator->trackingNumberHints(),
        ]);

        DB::table('package_tracker_carriers')
            ->where('code', 'ups')
            ->update([
                'driver' => $integrator->clientClass(),
                'api_base_url' => $carrier->api_base_url ?: $integrator->defaultBaseUrl(),
                'settings' => json_encode($settings),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
