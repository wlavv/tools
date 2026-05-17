<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wc_resource_fingerprints', function (Blueprint $table) {
            if (!Schema::hasColumn('wc_resource_fingerprints', 'short_hash')) {
                $table->string('short_hash', 40)->nullable()->after('hash_value')->index('wc_resource_fp_short_hash_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wc_resource_fingerprints', function (Blueprint $table) {
            if (Schema::hasColumn('wc_resource_fingerprints', 'short_hash')) {
                $table->dropIndex('wc_resource_fp_short_hash_idx');
                $table->dropColumn('short_hash');
            }
        });
    }
};
