<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_supplier_term_levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_supplier')->index();
            $table->string('name', 120);
            $table->decimal('min_amount', 15, 4)->default(0);
            $table->decimal('max_amount', 15, 4)->nullable();
            $table->decimal('discount_percent', 8, 4)->default(0);
            $table->boolean('free_shipping')->default(false);
            $table->string('currency_iso', 3)->default('EUR');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(100);
            $table->timestamps();

            $table->index(['id_supplier', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_supplier_term_levels');
    }
};
