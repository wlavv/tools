<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_tracker_carrier_suggestions', function (Blueprint $table) {
            $table->id();
            $table->string('client_key', 120)->nullable();
            $table->string('tracking_number', 120);
            $table->string('requested_carrier_code', 80)->nullable();
            $table->string('suggested_carrier_code', 80)->nullable();
            $table->string('status', 40)->default('open');
            $table->decimal('confidence', 5, 2)->default(0);
            $table->text('reason')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['client_key', 'status'], 'pt_suggestions_client_status_idx');
            $table->index(['tracking_number', 'suggested_carrier_code'], 'pt_suggestions_tracking_carrier_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_tracker_carrier_suggestions');
    }
};
