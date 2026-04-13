<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('title');
            $table->longText('message')->nullable();
            $table->string('type', 20)->default('info');
            $table->string('category', 80)->default('general');
            $table->string('priority', 20)->default('normal');
            $table->string('status', 20)->default('queued');
            $table->string('icon')->nullable();
            $table->string('action_label')->nullable();
            $table->text('action_url')->nullable();
            $table->string('source_module', 100)->nullable();
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['category', 'type']);
            $table->index(['source_module', 'reference_type', 'reference_id'], 'notifications_source_reference_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
