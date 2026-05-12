<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_document_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('name', 120);
            $table->string('icon', 120)->nullable();
            $table->string('color', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_supplier')->default(true);
            $table->boolean('affects_stock')->default(false);
            $table->boolean('affects_prices')->default(false);
            $table->boolean('is_financial')->default(false);
            $table->unsignedInteger('sort_order')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_document_types');
    }
};
