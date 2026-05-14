<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('streamdeck_access_points', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('name', 150);
            $table->string('slug', 160)->unique();
            $table->text('description')->nullable();
            $table->string('type', 32); // redirect | task
            $table->boolean('enabled')->default(true);
            $table->string('token_hash', 128);
            $table->string('token_hint', 16)->nullable();
            $table->string('target_url', 2048)->nullable();
            $table->string('task_key', 120)->nullable();
            $table->json('payload')->nullable();
            $table->json('allowed_ips')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_uses')->nullable();
            $table->unsignedInteger('use_count')->default(0);
            $table->unsignedInteger('cooldown_seconds')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->string('queue', 120)->nullable();
            $table->boolean('respond_json')->default(true);
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['enabled', 'type']);
            $table->index('task_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('streamdeck_access_points');
    }
};
