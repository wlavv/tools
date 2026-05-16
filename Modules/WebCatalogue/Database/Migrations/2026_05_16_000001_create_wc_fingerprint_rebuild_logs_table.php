<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wc_fingerprint_rebuild_logs')) {
            Schema::create('wc_fingerprint_rebuild_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_store')->index();
                $table->string('trigger', 40)->default('scheduled')->index();
                $table->string('status', 40)->default('queued')->index();
                $table->unsignedInteger('processed')->default(0);
                $table->unsignedInteger('created_count')->default(0);
                $table->unsignedInteger('updated_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->string('algorithm', 160)->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->unsignedInteger('duration_ms')->nullable();
                $table->text('error_message')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_fingerprint_rebuild_logs');
    }
};
