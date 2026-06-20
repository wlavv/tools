<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('infrastructure_backup_logs')) {
            return;
        }

        Schema::create('infrastructure_backup_logs', function (Blueprint $table) {
            $table->id();
            $table->string('server_name')->default('LSG AI Server')->index();
            $table->string('server_type', 40)->default('ai')->index();
            $table->string('action', 40)->index();
            $table->string('backup_filename')->nullable()->index();
            $table->unsignedBigInteger('backup_size')->nullable();
            $table->string('checksum', 128)->nullable();
            $table->string('status', 40)->default('ok')->index();
            $table->text('message')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable()->index();
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infrastructure_backup_logs');
    }
};
