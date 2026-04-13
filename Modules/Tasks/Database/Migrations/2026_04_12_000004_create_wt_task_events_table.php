<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wt_task_events')) {
            return;
        }

        Schema::create('wt_task_events', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();

            $table->date('event_date');
            $table->time('event_time')->nullable();
            $table->time('event_end_time')->nullable();

            $table->string('location')->nullable();
            $table->string('color', 20)->nullable();
            $table->string('icon', 100)->nullable();

            $table->boolean('all_day')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('event_date', 'idx_wt_task_events_event_date');
            $table->index('member_id', 'idx_wt_task_events_member_id');
            $table->index(['event_date', 'member_id'], 'idx_wt_task_events_date_member');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('wt_task_events')) {
            return;
        }

        Schema::table('wt_task_events', function (Blueprint $table) {
            $table->dropIndex('idx_wt_task_events_event_date');
            $table->dropIndex('idx_wt_task_events_member_id');
            $table->dropIndex('idx_wt_task_events_date_member');
        });

        Schema::dropIfExists('wt_task_events');
    }
};