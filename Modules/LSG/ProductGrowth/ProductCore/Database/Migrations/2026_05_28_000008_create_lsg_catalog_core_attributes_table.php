<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_core_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 140);
            $table->string('slug', 160)->unique();
            $table->string('data_type', 40)->default('text');
            $table->string('unit', 40)->nullable();
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_syncable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_core_attributes'); }
};
