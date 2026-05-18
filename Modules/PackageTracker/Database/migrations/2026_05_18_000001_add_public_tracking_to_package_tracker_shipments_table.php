<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('package_tracker_shipments', function (Blueprint $table) {
            if (! Schema::hasColumn('package_tracker_shipments', 'public_token')) {
                $table->string('public_token', 80)->nullable()->unique()->after('tracking_number');
            }

            if (! Schema::hasColumn('package_tracker_shipments', 'public_tracking_enabled')) {
                $table->boolean('public_tracking_enabled')->default(true)->index()->after('public_token');
            }

            if (! Schema::hasColumn('package_tracker_shipments', 'public_viewed_at')) {
                $table->timestamp('public_viewed_at')->nullable()->after('last_polled_at');
            }
        });

        DB::table('package_tracker_shipments')
            ->whereNull('public_token')
            ->orderBy('id')
            ->select('id')
            ->chunkById(100, function ($shipments) {
                foreach ($shipments as $shipment) {
                    DB::table('package_tracker_shipments')
                        ->where('id', $shipment->id)
                        ->update([
                            'public_token' => Str::random(48),
                            'public_tracking_enabled' => true,
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('package_tracker_shipments', function (Blueprint $table) {
            if (Schema::hasColumn('package_tracker_shipments', 'public_viewed_at')) {
                $table->dropColumn('public_viewed_at');
            }

            if (Schema::hasColumn('package_tracker_shipments', 'public_tracking_enabled')) {
                $table->dropColumn('public_tracking_enabled');
            }

            if (Schema::hasColumn('package_tracker_shipments', 'public_token')) {
                $table->dropUnique(['public_token']);
                $table->dropColumn('public_token');
            }
        });
    }
};
