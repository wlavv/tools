<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lsg_catalog_core_characteristics')) {
            Schema::create('lsg_catalog_core_characteristics', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 140);
                $table->string('slug', 160)->unique();
                $table->string('data_type', 40)->default('text');
                $table->string('unit', 40)->nullable();
                $table->boolean('is_filterable')->default(true);
                $table->boolean('is_searchable')->default(true);
                $table->boolean('is_seo_keyword')->default(true);
                $table->boolean('is_syncable')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('lsg_catalog_core_product_characteristics')) {
            Schema::create('lsg_catalog_core_product_characteristics', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('characteristic_id');
                $table->text('value')->nullable();
                $table->json('value_json')->nullable();
                $table->timestamps();
                $table->unique(['product_id', 'characteristic_id'], 'lsg_catalog_product_characteristic_unique');
                $table->foreign('product_id', 'lsg_pg_char_product_fk')->references('id')->on('lsg_catalog_core_products')->cascadeOnDelete();
                $table->foreign('characteristic_id', 'lsg_pg_char_def_fk')->references('id')->on('lsg_catalog_core_characteristics')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lsg_catalog_core_product_characteristics');
        Schema::dropIfExists('lsg_catalog_core_characteristics');
    }
};
