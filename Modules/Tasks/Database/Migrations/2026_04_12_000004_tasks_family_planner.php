<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wt_task_members') && ! Schema::hasColumn('wt_task_members', 'profile_image')) {
            Schema::table('wt_task_members', function (Blueprint $table) {
                $table->string('profile_image')->nullable()->after('color');
            });
        }

        if (! Schema::hasTable('wt_task_events')) {
            Schema::create('wt_task_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->date('event_date');
                $table->time('event_time')->nullable();
                $table->string('color')->nullable();
                $table->string('icon')->nullable();
                $table->timestamps();

                $table->index(['event_date']);
                $table->index(['member_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wt_task_events')) {
            Schema::dropIfExists('wt_task_events');
        }

        if (Schema::hasTable('wt_task_members') && Schema::hasColumn('wt_task_members', 'profile_image')) {
            Schema::table('wt_task_members', function (Blueprint $table) {
                $table->dropColumn('profile_image');
            });
        }
    }
};
