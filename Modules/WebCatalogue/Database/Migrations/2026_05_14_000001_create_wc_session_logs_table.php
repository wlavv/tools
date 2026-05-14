<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wc_session_logs')) {
            return;
        }

        Schema::create('wc_session_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_store')->nullable()->index();
            $table->unsignedBigInteger('id_product')->nullable()->index();
            $table->string('session_token', 120)->nullable()->index();
            $table->string('event', 120)->index();
            $table->text('url')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wc_session_logs');
    }
};
