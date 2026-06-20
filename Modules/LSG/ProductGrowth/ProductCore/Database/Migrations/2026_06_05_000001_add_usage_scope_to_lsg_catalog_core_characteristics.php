<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lsg_catalog_core_characteristics') && !Schema::hasColumn('lsg_catalog_core_characteristics', 'usage_scope')) {
            Schema::table('lsg_catalog_core_characteristics', function (Blueprint $table): void {
                $table->string('usage_scope', 40)->default('product')->after('data_type');
            });
        }

        if (Schema::hasTable('lsg_catalog_core_characteristics') && Schema::hasColumn('lsg_catalog_core_characteristics', 'usage_scope')) {
            DB::table('lsg_catalog_core_characteristics')
                ->whereIn('slug', ['condition', 'language', 'finish', 'version_treatment'])
                ->update(['usage_scope' => 'combination']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('lsg_catalog_core_characteristics') && Schema::hasColumn('lsg_catalog_core_characteristics', 'usage_scope')) {
            Schema::table('lsg_catalog_core_characteristics', function (Blueprint $table): void {
                $table->dropColumn('usage_scope');
            });
        }
    }
};
