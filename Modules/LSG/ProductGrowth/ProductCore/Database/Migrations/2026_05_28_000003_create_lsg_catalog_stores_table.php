<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_stores', function (Blueprint $table) {
            $table->id();
            $table->string('name', 160);
            $table->string('slug', 180)->unique();
            $table->string('domain', 180)->nullable();
            $table->string('store_code', 60)->nullable()->index();
            $table->string('default_language', 10)->default('pt');
            $table->string('default_currency', 3)->default('EUR');
            $table->boolean('is_active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_stores'); }
};
