<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_core_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('vat_number', 60)->nullable()->index();
            $table->string('email', 180)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('country', 80)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_core_suppliers'); }
};
