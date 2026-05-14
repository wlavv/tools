<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_stores') || Schema::hasColumn('catalog_stores', 'site_kind')) {
            return;
        }

        Schema::table('catalog_stores', function (Blueprint $table) {
            $table->string('site_kind', 24)->default('store')->after('record_type')->index();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('catalog_stores') || !Schema::hasColumn('catalog_stores', 'site_kind')) {
            return;
        }

        Schema::table('catalog_stores', function (Blueprint $table) {
            $table->dropColumn('site_kind');
        });
    }
};
