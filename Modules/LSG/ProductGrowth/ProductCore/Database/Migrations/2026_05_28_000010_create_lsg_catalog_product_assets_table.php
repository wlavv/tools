<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_product_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('lsg_catalog_core_products')->cascadeOnDelete();
            $table->foreignId('store_product_id')->nullable()->constrained('lsg_catalog_store_products')->cascadeOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('lsg_catalog_stores')->nullOnDelete();
            $table->string('asset_type', 60)->index();
            $table->string('asset_role', 80)->index();
            $table->string('source_module', 120)->nullable();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('title', 180)->nullable();
            $table->string('file_path', 500)->nullable();
            $table->string('public_url', 500)->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->string('extension', 20)->nullable();
            $table->string('language', 10)->nullable();
            $table->boolean('is_public')->default(false)->index();
            $table->boolean('is_primary')->default(false)->index();
            $table->boolean('is_syncable_to_prestashop')->default(false)->index();
            $table->boolean('is_syncable_to_webcatalogue')->default(false)->index();
            $table->enum('approval_status', ['draft','pending_review','approved','rejected','archived'])->default('draft')->index();
            $table->enum('brand_compliance_status', ['not_checked','approved','needs_review','blocked'])->default('not_checked')->index();
            $table->decimal('quality_score', 5, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamp('approved_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_product_assets'); }
};
