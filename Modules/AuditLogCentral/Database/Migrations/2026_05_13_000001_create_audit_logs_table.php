<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 64)->unique();
            $table->string('module', 100)->index();
            $table->string('event', 150)->index();
            $table->string('action', 100)->nullable()->index();
            $table->string('severity', 30)->default('info')->index();
            $table->string('status', 30)->default('success')->index();
            $table->nullableMorphs('auditable');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable()->index();
            $table->string('ip_address', 64)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('request_method', 10)->nullable();
            $table->string('request_url', 1024)->nullable();
            $table->string('source', 100)->nullable()->index();
            $table->string('correlation_id', 100)->nullable()->index();
            $table->json('metadata')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['module', 'event', 'occurred_at']);
            $table->index(['auditable_type', 'auditable_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
