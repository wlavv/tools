<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('wt_investments_position_events')) return;

        Schema::create('wt_investments_position_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('position_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // stop_hit, step_moved, manual_close, price_update, etc.
            $table->decimal('price', 18, 4)->nullable();
            $table->json('data')->nullable();
            $table->timestamp('event_time')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_investments_position_events');
    }
};
