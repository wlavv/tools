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

        Schema::table('wc_resource_fingerprints', function (Blueprint $table) {
            if (!Schema::hasColumn('wc_resource_fingerprints', 'short_hash_prefix')) {
                $table->string('short_hash_prefix', 12)->nullable()->after('short_hash')->index('wc_resource_fp_short_prefix_idx');
            }

            if (!Schema::hasColumn('wc_resource_fingerprints', 'aspect_ratio_bucket')) {
                $table->unsignedSmallInteger('aspect_ratio_bucket')->nullable()->after('height')->index('wc_resource_fp_aspect_bucket_idx');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wc_resource_fingerprints', function (Blueprint $table) {
            if (Schema::hasColumn('wc_resource_fingerprints', 'short_hash_prefix')) {
                $table->dropIndex('wc_resource_fp_short_prefix_idx');
                $table->dropColumn('short_hash_prefix');
            }

            if (Schema::hasColumn('wc_resource_fingerprints', 'aspect_ratio_bucket')) {
                $table->dropIndex('wc_resource_fp_aspect_bucket_idx');
                $table->dropColumn('aspect_ratio_bucket');
            }
        });
    }
};
