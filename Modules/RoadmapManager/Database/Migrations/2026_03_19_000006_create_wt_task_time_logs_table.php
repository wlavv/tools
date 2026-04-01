<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_task_time_logs')) {
            Schema::create('wt_task_time_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->unsignedBigInteger('user_id');
                $table->decimal('logged_hours', 6, 2);
                $table->date('log_date');
                $table->text('description')->nullable();
                $table->timestamps();

                $table->index('task_id', 'idx_task');
                $table->index('user_id', 'idx_user');
                $table->index('log_date', 'idx_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_task_time_logs');
    }
};
