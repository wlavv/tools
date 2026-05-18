<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('package_tracker_shipments', function (Blueprint $table) {
            if (! Schema::hasColumn('package_tracker_shipments', 'client_key')) {
                $table->string('client_key', 120)->nullable()->index()->after('carrier_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('package_tracker_shipments', function (Blueprint $table) {
            if (Schema::hasColumn('package_tracker_shipments', 'client_key')) {
                $table->dropColumn('client_key');
            }
        });
    }
};
