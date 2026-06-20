<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_store_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->nullable()->constrained('lsg_catalog_stores')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('lsg_catalog_store_categories')->nullOnDelete();
            $table->string('name', 180);
            $table->string('slug', 200)->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['store_id','slug'], 'lsg_catalog_store_categories_unique');
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_store_categories'); }
};
