<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('backoffice_acknowledgements')) {
            return;
        }

        Schema::create('backoffice_acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('source_type', 120);
            $table->string('source_id', 191);
            $table->string('source_hash', 40);
            $table->string('status', 40)->default('acknowledged');
            $table->timestamp('acknowledged_at')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'source_hash'], 'bo_ack_user_source_hash_unique');
            $table->index(['source_type', 'status'], 'bo_ack_source_status_idx');
            $table->index('acknowledged_at', 'bo_ack_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backoffice_acknowledgements');
    }
};
