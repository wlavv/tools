<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_core_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('external_reference', 120)->nullable()->index();
            $table->string('website', 255)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_core_brands'); }
};
