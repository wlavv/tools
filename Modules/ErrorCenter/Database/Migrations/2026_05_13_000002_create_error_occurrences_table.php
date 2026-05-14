<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_occurrences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('error_event_id')->constrained('error_events')->cascadeOnDelete();

            $table->timestamp('occurred_at')->useCurrent()->index();

            $table->string('user_id', 100)->nullable()->index();
            $table->string('tenant_id', 100)->nullable()->index();

            $table->string('request_id', 100)->nullable()->index();
            $table->string('correlation_id', 100)->nullable()->index();

            $table->string('endpoint', 500)->nullable()->index();
            $table->string('http_method', 20)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable()->index();

            $table->string('ip_address', 100)->nullable();
            $table->text('user_agent')->nullable();

            $table->longText('stack_trace')->nullable();
            $table->json('payload_snapshot')->nullable();
            $table->json('context_json')->nullable();

            $table->timestamps();

            $table->index(['error_event_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_occurrences');
    }
};
