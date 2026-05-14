<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_import_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('profile_key');
            $table->string('profile_class')->nullable();
            $table->string('status')->default('uploaded');
            $table->string('mode')->default('upsert');
            $table->string('original_filename')->nullable();
            $table->string('disk')->default('local');
            $table->string('path')->nullable();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->unsignedInteger('warning_rows')->default(0);
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['profile_key', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_import_batches');
    }
};
