<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_manufacturer_stores')) {
            Schema::create('catalog_manufacturer_stores', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('manufacturer_id')->index();
                $table->unsignedBigInteger('store_id')->index();
                $table->timestamps();
                $table->unique(['manufacturer_id', 'store_id'], 'cat_manu_store_unique');
            });
        }

        if (!Schema::hasTable('catalog_supplier_stores')) {
            Schema::create('catalog_supplier_stores', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_id')->index();
                $table->unsignedBigInteger('store_id')->index();
                $table->timestamps();
                $table->unique(['supplier_id', 'store_id'], 'cat_supplier_store_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_supplier_stores');
        Schema::dropIfExists('catalog_manufacturer_stores');
    }
};
