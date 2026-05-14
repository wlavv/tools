<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('data_import_batches')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('raw_data')->nullable();
            $table->json('normalized_data')->nullable();
            $table->string('status')->default('pending');
            $table->json('errors')->nullable();
            $table->json('warnings')->nullable();
            $table->string('operation')->nullable();
            $table->string('target_model')->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'status']);
            $table->index(['target_model', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_import_rows');
    }
};
