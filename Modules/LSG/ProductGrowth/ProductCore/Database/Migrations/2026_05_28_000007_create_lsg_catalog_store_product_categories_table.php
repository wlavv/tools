<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_store_product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_product_id')->constrained('lsg_catalog_store_products')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('lsg_catalog_store_categories')->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->unique(['store_product_id','category_id'], 'lsg_catalog_store_product_category_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_store_product_categories'); }
};
