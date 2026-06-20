<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_core_product_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('lsg_catalog_core_products')->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('lsg_catalog_core_attributes')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->json('value_json')->nullable();
            $table->timestamps();
            $table->unique(['product_id','attribute_id'], 'lsg_catalog_product_attribute_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_core_product_attributes'); }
};
