<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_tracker_shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrier_id')->constrained('package_tracker_carriers')->cascadeOnDelete();
            $table->string('tracking_number', 120);
            $table->string('external_reference')->nullable()->index();
            $table->string('store_code', 80)->nullable()->index();
            $table->string('order_reference', 120)->nullable()->index();
            $table->string('customer_email')->nullable()->index();
            $table->string('destination_country', 2)->nullable()->index();
            $table->string('status', 40)->default('pending')->index();
            $table->string('substatus')->nullable();
            $table->string('last_location')->nullable();
            $table->timestamp('estimated_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('last_event_at')->nullable()->index();
            $table->timestamp('last_polled_at')->nullable();
            $table->timestamp('next_poll_at')->nullable()->index();
            $table->timestamp('sla_due_at')->nullable()->index();
            $table->boolean('is_stale')->default(false)->index();
            $table->boolean('has_exception')->default(false)->index();
            $table->json('metadata')->nullable();
            $table->unsignedSmallInteger('poll_attempts')->default(0);
            $table->timestamps();

            $table->unique(['carrier_id', 'tracking_number'], 'pkg_tracker_carrier_tracking_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_tracker_shipments');
    }
};
