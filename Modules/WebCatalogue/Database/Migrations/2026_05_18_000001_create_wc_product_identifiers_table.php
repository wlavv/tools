<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wc_product_identifiers')) {
            Schema::create('wc_product_identifiers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->unsignedBigInteger('id_product')->index();
                $table->string('type', 60)->index();
                $table->string('value', 500);
                $table->string('normalized_value', 190);
                $table->string('source', 80)->default('product_sync')->index();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['id_store', 'type', 'normalized_value'], 'wc_product_identifiers_store_type_value_unique');
                $table->index(['id_store', 'normalized_value'], 'wc_product_identifiers_store_value_idx');
                $table->index(['type', 'normalized_value'], 'wc_product_identifiers_type_value_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_product_identifiers');
    }
};
