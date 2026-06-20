<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('catalog_combination_attributes')) {
            Schema::create('catalog_combination_attributes', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 140);
                $table->string('slug', 160)->unique();
                $table->string('display_type', 40)->default('select');
                $table->boolean('is_required')->default(true);
                $table->boolean('affects_price')->default(true);
                $table->boolean('affects_stock')->default(true);
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('catalog_combination_attribute_values')) {
            Schema::create('catalog_combination_attribute_values', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('attribute_id');
                $table->string('value', 180);
                $table->string('label', 180);
                $table->unsignedInteger('position')->default(0);
                $table->boolean('active')->default(true)->index();
                $table->timestamps();

                $table->unique(['attribute_id', 'value'], 'catalog_comb_attr_values_unique');
                $table->index('attribute_id', 'catalog_comb_attr_values_attr_idx');
                $table->foreign('attribute_id', 'catalog_comb_attr_values_attr_fk')
                    ->references('id')
                    ->on('catalog_combination_attributes')
                    ->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('catalog_category_combination_attributes')) {
            Schema::create('catalog_category_combination_attributes', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('store_category_id');
                $table->unsignedBigInteger('attribute_id');
                $table->boolean('is_required')->default(true);
                $table->unsignedInteger('position')->default(0);
                $table->timestamps();

                $table->unique(['store_category_id', 'attribute_id'], 'catalog_cat_comb_attr_unique');
                $table->index('store_category_id', 'catalog_cat_comb_attr_cat_idx');
                $table->index('attribute_id', 'catalog_cat_comb_attr_attr_idx');

                if (Schema::hasTable('catalog_store_categories')) {
                    $table->foreign('store_category_id', 'catalog_cat_comb_attr_cat_fk')
                        ->references('id')
                        ->on('catalog_store_categories')
                        ->cascadeOnDelete();
                }

                $table->foreign('attribute_id', 'catalog_cat_comb_attr_attr_fk')
                    ->references('id')
                    ->on('catalog_combination_attributes')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_category_combination_attributes');
        Schema::dropIfExists('catalog_combination_attribute_values');
        Schema::dropIfExists('catalog_combination_attributes');
    }
};
