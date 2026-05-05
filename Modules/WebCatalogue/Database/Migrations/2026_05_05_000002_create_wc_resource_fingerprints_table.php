<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wc_resource_fingerprints')) {
            Schema::create('wc_resource_fingerprints', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->nullable()->index();
                $table->unsignedBigInteger('id_product')->nullable()->index();
                $table->unsignedBigInteger('id_resource')->index();
                $table->string('algorithm', 120)->index();
                $table->text('hash_value')->nullable();
                $table->json('vector_json')->nullable();
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->string('source_signature', 190)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->unique(['id_resource', 'algorithm'], 'wc_resource_fp_resource_algorithm_unique');
                $table->index(['id_store', 'id_product', 'algorithm'], 'wc_resource_fp_store_product_algorithm_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_resource_fingerprints');
    }
};
