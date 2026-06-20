<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lsg_catalog_category_characteristics')) {
            return;
        }

        Schema::create('lsg_catalog_category_characteristics', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('store_category_id');
            $table->unsignedBigInteger('characteristic_id');
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->string('section', 120)->nullable();
            $table->timestamps();

            $table->unique(['store_category_id', 'characteristic_id'], 'lsg_pg_cat_char_unique');
            $table->index('store_category_id', 'lsg_pg_cat_char_cat_idx');
            $table->index('characteristic_id', 'lsg_pg_cat_char_def_idx');

            if (Schema::hasTable('catalog_store_categories')) {
                $table->foreign('store_category_id', 'lsg_pg_cat_char_cat_fk')
                    ->references('id')
                    ->on('catalog_store_categories')
                    ->cascadeOnDelete();
            }

            if (Schema::hasTable('lsg_catalog_core_characteristics')) {
                $table->foreign('characteristic_id', 'lsg_pg_cat_char_def_fk')
                    ->references('id')
                    ->on('lsg_catalog_core_characteristics')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lsg_catalog_category_characteristics');
    }
};
