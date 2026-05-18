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

        DB::table('package_tracker_carriers')
            ->where('code', 'dpd')
            ->whereIn('api_base_url', [
                'https://api-test.dpd.com',
                'http://api-test.dpd.com',
            ])
            ->update([
                'api_base_url' => null,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        //
    }
};
