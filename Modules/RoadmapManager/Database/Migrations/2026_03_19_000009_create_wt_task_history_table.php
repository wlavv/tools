<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_task_history')) {
            Schema::create('wt_task_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('action', 50);
                $table->string('field_name', 100)->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->index('task_id', 'idx_task');
                $table->index('user_id', 'idx_user');
                $table->index('action', 'idx_action');
                $table->index('created_at', 'idx_created');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_task_history');
    }
};
