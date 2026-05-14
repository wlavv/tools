<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_events', function (Blueprint $table): void {
            $table->id();
            $table->string('hash', 128)->unique();

            $table->string('title', 255);
            $table->text('message')->nullable();
            $table->string('error_type', 255)->nullable();

            $table->string('severity', 50)->default('error')->index();
            $table->string('status', 50)->default('new')->index();

            $table->string('module', 100)->nullable()->index();
            $table->string('source', 50)->default('backend')->index();
            $table->string('environment', 50)->default('production')->index();

            $table->timestamp('first_seen_at')->useCurrent()->index();
            $table->timestamp('last_seen_at')->useCurrent()->index();

            $table->unsignedInteger('occurrence_count')->default(1);
            $table->unsignedInteger('affected_users_count')->default(0);

            $table->string('assigned_to', 100)->nullable()->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->string('resolved_by', 100)->nullable()->index();

            $table->timestamp('last_notification_sent_at')->nullable();
            $table->unsignedInteger('notification_count')->default(0);
            $table->string('last_notification_event', 100)->nullable();

            $table->timestamps();

            $table->index(['status', 'severity']);
            $table->index(['environment', 'severity', 'status']);
            $table->index(['module', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_events');
    }
};
