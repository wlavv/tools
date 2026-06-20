<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lsg_catalog_core_products')) {
            return;
        }

        if (Schema::hasColumn('lsg_catalog_core_products', 'brand_id') && Schema::hasTable('catalog_core_manufacturers')) {
            DB::table('lsg_catalog_core_products')
                ->whereNotNull('brand_id')
                ->whereNotIn('brand_id', DB::table('catalog_core_manufacturers')->select('id'))
                ->update(['brand_id' => null]);
        }

        if (Schema::hasColumn('lsg_catalog_core_products', 'supplier_id') && Schema::hasTable('catalog_core_suppliers')) {
            DB::table('lsg_catalog_core_products')
                ->whereNotNull('supplier_id')
                ->whereNotIn('supplier_id', DB::table('catalog_core_suppliers')->select('id'))
                ->update(['supplier_id' => null]);
        }

        Schema::table('lsg_catalog_core_products', function (Blueprint $table): void {
            if (Schema::hasColumn('lsg_catalog_core_products', 'brand_id') && Schema::hasTable('catalog_core_manufacturers')) {
                $table->foreign('brand_id', 'lsg_pg_products_brand_catalog_fk')
                    ->references('id')
                    ->on('catalog_core_manufacturers')
                    ->nullOnDelete();
            }

            if (Schema::hasColumn('lsg_catalog_core_products', 'supplier_id') && Schema::hasTable('catalog_core_suppliers')) {
                $table->foreign('supplier_id', 'lsg_pg_products_supplier_catalog_fk')
                    ->references('id')
                    ->on('catalog_core_suppliers')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('lsg_catalog_core_products')) {
            return;
        }

        Schema::table('lsg_catalog_core_products', function (Blueprint $table): void {
            foreach (['lsg_pg_products_brand_catalog_fk', 'lsg_pg_products_supplier_catalog_fk'] as $foreignKey) {
                try {
                    $table->dropForeign($foreignKey);
                } catch (Throwable) {
                    // The FK may already be absent in older installs.
                }
            }
        });
    }
};
