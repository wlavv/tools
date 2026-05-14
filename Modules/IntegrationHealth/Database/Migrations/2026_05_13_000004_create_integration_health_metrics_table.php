<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('integration_health_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('integration_health_services')->nullOnDelete();
            $table->string('service_slug')->index();
            $table->string('metric')->index();
            $table->decimal('value', 14, 4)->default(0);
            $table->string('unit')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('recorded_at')->index();
            $table->timestamps();

            $table->index(['service_slug', 'metric', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_health_metrics');
    }
};
