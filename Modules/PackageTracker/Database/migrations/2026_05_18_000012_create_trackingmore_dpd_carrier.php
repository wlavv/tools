<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\PackageTracker\Services\Carriers\Integrators\TrackingMoreDpdIntegrator;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('package_tracker_carriers')) {
            return;
        }

        $integrator = new TrackingMoreDpdIntegrator();
        $exists = DB::table('package_tracker_carriers')->where('code', $integrator->code())->exists();

        DB::table('package_tracker_carriers')->updateOrInsert(['code' => $integrator->code()], [
            'name' => $integrator->name(),
            'driver' => $integrator->clientClass(),
            'api_base_url' => $integrator->defaultBaseUrl(),
            'supports_webhooks' => $integrator->supportsWebhooks(),
            'is_active' => true,
            'settings' => json_encode(array_merge($integrator->defaultSettings(), [
                '_integrator' => [
                    'code' => $integrator->code(),
                    'capabilities' => $integrator->capabilities(),
                    'credential_schema' => $integrator->credentialSchema(),
                    'tracking_number_hints' => $integrator->trackingNumberHints(),
                ],
            ]), JSON_THROW_ON_ERROR),
            'updated_at' => now(),
            ...($exists ? [] : ['created_at' => now()]),
        ]);
    }

    public function down(): void
    {
        //
    }
};
