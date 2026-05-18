<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('package_tracker_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipment_id')->constrained('package_tracker_shipments')->cascadeOnDelete();
            $table->foreignId('carrier_id')->constrained('package_tracker_carriers')->cascadeOnDelete();
            $table->string('carrier_event_id')->nullable()->index();
            $table->string('raw_status')->nullable();
            $table->string('normalized_status', 40)->default('unknown')->index();
            $table->string('substatus')->nullable();
            $table->text('description')->nullable();
            $table->string('location')->nullable();
            $table->timestamp('event_at')->nullable()->index();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['shipment_id', 'event_at'], 'pkg_tracker_event_shipment_at_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_tracker_events');
    }
};
