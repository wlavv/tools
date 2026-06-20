<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lsg_catalog_category_characteristics') && !Schema::hasColumn('lsg_catalog_category_characteristics', 'allowed_values')) {
            Schema::table('lsg_catalog_category_characteristics', function (Blueprint $table): void {
                $table->json('allowed_values')->nullable()->after('is_required');
            });
        }

        if (Schema::hasTable('catalog_category_combination_attributes') && !Schema::hasColumn('catalog_category_combination_attributes', 'allowed_values')) {
            Schema::table('catalog_category_combination_attributes', function (Blueprint $table): void {
                $table->json('allowed_values')->nullable()->after('is_required');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('catalog_category_combination_attributes') && Schema::hasColumn('catalog_category_combination_attributes', 'allowed_values')) {
            Schema::table('catalog_category_combination_attributes', function (Blueprint $table): void {
                $table->dropColumn('allowed_values');
            });
        }

        if (Schema::hasTable('lsg_catalog_category_characteristics') && Schema::hasColumn('lsg_catalog_category_characteristics', 'allowed_values')) {
            Schema::table('lsg_catalog_category_characteristics', function (Blueprint $table): void {
                $table->dropColumn('allowed_values');
            });
        }
    }
};
