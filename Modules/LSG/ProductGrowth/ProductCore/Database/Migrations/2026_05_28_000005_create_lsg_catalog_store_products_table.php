<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_store_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('lsg_catalog_core_products')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('lsg_catalog_stores')->cascadeOnDelete();
            $table->string('name', 220)->nullable();
            $table->text('short_description')->nullable();
            $table->longText('description')->nullable();
            $table->string('seo_title', 255)->nullable();
            $table->text('seo_description')->nullable();
            $table->decimal('sale_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->decimal('margin_percentage', 7, 2)->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->boolean('active_for_sale')->default(false)->index();
            $table->boolean('sync_to_prestashop')->default(false)->index();
            $table->enum('sync_status', ['not_synced','ready_to_sync','syncing','synced','needs_resync','sync_failed','conflict'])->default('not_synced')->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->string('internal_hash', 128)->nullable();
            $table->string('external_hash', 128)->nullable();
            $table->json('store_overrides')->nullable();
            $table->timestamps();
            $table->unique(['product_id','store_id'], 'lsg_catalog_store_products_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_store_products'); }
};
