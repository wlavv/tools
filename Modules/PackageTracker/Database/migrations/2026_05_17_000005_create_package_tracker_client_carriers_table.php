<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_tracker_client_carriers', function (Blueprint $table) {
            $table->id();
            $table->string('client_key', 120);
            $table->string('carrier_code', 80);
            $table->boolean('is_enabled')->default(true);
            $table->json('credentials')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
            $table->timestamps();

            $table->unique(['client_key', 'carrier_code'], 'pt_client_carrier_unique');
            $table->index(['client_key', 'is_enabled'], 'pt_client_carrier_enabled_idx');
            $table->index('carrier_code', 'pt_client_carrier_code_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_tracker_client_carriers');
    }
};
