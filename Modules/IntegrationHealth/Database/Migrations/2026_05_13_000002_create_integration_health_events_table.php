<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_health_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('integration_health_services')->nullOnDelete();
            $table->string('service_slug')->nullable()->index();
            $table->string('severity')->default('info')->index();
            $table->string('event_type')->index();
            $table->string('title');
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamps();

            $table->index(['severity', 'resolved_at']);
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_health_events');
    }
};
