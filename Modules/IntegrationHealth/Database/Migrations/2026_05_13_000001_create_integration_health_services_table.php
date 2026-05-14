<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_health_services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('type')->default('api')->index();
            $table->string('status')->default('unknown')->index();
            $table->unsignedTinyInteger('health_score')->default(100);
            $table->unsignedInteger('avg_response_time_ms')->nullable();
            $table->decimal('error_rate', 8, 2)->default(0);
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_error_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->json('meta')->nullable();
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_health_services');
    }
};
