<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('erp_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('scope', 80)->index();
            $table->string('code', 80);
            $table->string('name', 120);
            $table->string('icon', 120)->nullable();
            $table->string('color', 30)->nullable();
            $table->string('badge_class', 80)->nullable();
            $table->boolean('is_initial')->default(false);
            $table->boolean('is_final')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(100);
            $table->timestamps();

            $table->unique(['scope', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('erp_statuses');
    }
};
