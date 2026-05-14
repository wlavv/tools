<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_stores') || Schema::hasColumn('catalog_stores', 'record_type')) {
            return;
        }

        Schema::table('catalog_stores', function (Blueprint $table) {
            $table->string('record_type', 24)->default('store')->after('domain')->index();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('catalog_stores') || !Schema::hasColumn('catalog_stores', 'record_type')) {
            return;
        }

        Schema::table('catalog_stores', function (Blueprint $table) {
            $table->dropColumn('record_type');
        });
    }
};
