<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lsg_catalog_core_characteristic_values')) {
            return;
        }

        Schema::table('lsg_catalog_core_characteristic_values', function (Blueprint $table): void {
            if (!Schema::hasColumn('lsg_catalog_core_characteristic_values', 'image_url')) {
                $table->string('image_url', 2048)->nullable()->after('label');
            }

            if (!Schema::hasColumn('lsg_catalog_core_characteristic_values', 'image_alt')) {
                $table->string('image_alt', 180)->nullable()->after('image_url');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lsg_catalog_core_characteristic_values')) {
            return;
        }

        Schema::table('lsg_catalog_core_characteristic_values', function (Blueprint $table): void {
            if (Schema::hasColumn('lsg_catalog_core_characteristic_values', 'image_alt')) {
                $table->dropColumn('image_alt');
            }

            if (Schema::hasColumn('lsg_catalog_core_characteristic_values', 'image_url')) {
                $table->dropColumn('image_url');
            }
        });
    }
};
