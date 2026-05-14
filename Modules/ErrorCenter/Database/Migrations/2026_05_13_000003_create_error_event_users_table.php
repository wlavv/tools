<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_event_users', function (Blueprint $table): void {
            $table->foreignId('error_event_id')->constrained('error_events')->cascadeOnDelete();
            $table->string('user_id', 100);
            $table->timestamp('first_seen_at')->useCurrent();

            $table->primary(['error_event_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_event_users');
    }
};
