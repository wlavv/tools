<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\PackageTracker\Services\Carriers\Drivers\DpdTrackingClient;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('package_tracker_carriers')) {
            return;
        }

        $carrier = DB::table('package_tracker_carriers')->where('code', 'dpd')->first();

        if (! $carrier) {
            return;
        }

        $settings = json_decode((string) $carrier->settings, true) ?: [];
        $settings = array_merge($settings, [
            'tracking_path' => 'status/tracking',
            'tracking_param' => 'pknr',
            'method' => 'GET',
            'detail' => $settings['detail'] ?? '3',
            'show_all' => $settings['show_all'] ?? '1',
            'lang' => $settings['lang'] ?? 'en',
        ]);

        DB::table('package_tracker_carriers')
            ->where('code', 'dpd')
            ->update([
                'driver' => DpdTrackingClient::class,
                'api_base_url' => in_array($carrier->api_base_url, ['https://api-test.dpd.com', 'http://api-test.dpd.com'], true) ? null : $carrier->api_base_url,
                'settings' => json_encode($settings, JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
