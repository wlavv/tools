<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_health_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('integration_health_services')->nullOnDelete();
            $table->string('service_slug')->index();
            $table->timestamp('heartbeat_at')->index();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->string('status')->default('online')->index();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_health_heartbeats');
    }
};
