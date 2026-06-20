<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_core_products', function (Blueprint $table) {
            $table->id();
            $table->string('internal_sku', 120)->unique();
            $table->string('reference', 120)->nullable()->index();
            $table->string('ean', 80)->nullable()->index();
            $table->string('mpn', 120)->nullable()->index();
            $table->foreignId('brand_id')->nullable()->constrained('lsg_catalog_core_brands')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('lsg_catalog_core_suppliers')->nullOnDelete();
            $table->string('name', 220);
            $table->text('description')->nullable();
            $table->string('product_type', 80)->default('standard')->index();
            $table->decimal('base_cost', 12, 2)->nullable();
            $table->decimal('base_price', 12, 2)->nullable();
            $table->decimal('weight', 10, 3)->nullable();
            $table->decimal('width', 10, 3)->nullable();
            $table->decimal('height', 10, 3)->nullable();
            $table->decimal('depth', 10, 3)->nullable();
            $table->enum('status', ['draft','in_review','approved','ready_to_sync','synced','needs_resync','archived','blocked'])->default('draft')->index();
            $table->decimal('data_quality_score', 5, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_core_products'); }
};
