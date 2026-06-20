<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lsg_catalog_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('filename', 255)->nullable();
            $table->string('source', 120)->nullable();
            $table->foreignId('store_id')->nullable()->constrained('lsg_catalog_stores')->nullOnDelete();
            $table->unsignedInteger('rows_total')->default(0);
            $table->unsignedInteger('rows_imported')->default(0);
            $table->unsignedInteger('rows_failed')->default(0);
            $table->enum('status', ['pending','processing','completed','failed'])->default('pending')->index();
            $table->json('errors')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('lsg_catalog_import_batches'); }
};
