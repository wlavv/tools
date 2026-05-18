<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        $settings['api_type'] = $settings['api_type'] ?? 'status_tracking';

        DB::table('package_tracker_carriers')
            ->where('code', 'dpd')
            ->update([
                'settings' => json_encode($settings, JSON_THROW_ON_ERROR),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
