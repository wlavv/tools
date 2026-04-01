<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_task_items')) {
            Schema::create('wt_task_items', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedInteger('project_id');
                $table->unsignedBigInteger('milestone_id')->nullable();
                $table->unsignedTinyInteger('level')->default(1);
                $table->string('path', 1000)->default('');
                $table->unsignedTinyInteger('depth')->default(0);
                $table->string('code', 30)->nullable();
                $table->string('title', 300);
                $table->longText('description')->nullable();
                $table->enum('status', ['backlog','todo','in_progress','in_review','blocked','completed','cancelled','deferred'])->default('backlog');
                $table->enum('priority', ['low','medium','high','critical'])->default('medium');
                $table->unsignedTinyInteger('progress_percentage')->default(0);
                $table->boolean('auto_progress')->default(true);
                $table->date('planned_start_date')->nullable();
                $table->date('planned_end_date')->nullable();
                $table->date('actual_start_date')->nullable();
                $table->date('actual_end_date')->nullable();
                $table->date('deadline')->nullable();
                $table->decimal('estimated_hours', 8, 2)->nullable();
                $table->decimal('logged_hours', 8, 2)->default(0);
                $table->decimal('remaining_hours', 8, 2)->nullable();
                $table->unsignedBigInteger('assigned_to')->nullable();
                $table->unsignedBigInteger('assigned_team')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->enum('risk_level', ['none','low','medium','high','critical'])->default('none');
                $table->text('risk_notes')->nullable();
                $table->boolean('is_milestone_marker')->default(false);
                $table->boolean('is_recurring')->default(false);
                $table->string('recurrence_rule', 200)->nullable();
                $table->json('tags')->nullable();
                $table->json('custom_fields')->nullable();
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->softDeletes();

                $table->index('parent_id', 'idx_parent_id');
                $table->index('project_id', 'idx_project_id');
                $table->index('milestone_id', 'idx_milestone_id');
                $table->index('status', 'idx_status');
                $table->index('priority', 'idx_priority');
                $table->index('assigned_to', 'idx_assigned_to');
                $table->index('level', 'idx_level');
                $table->index('deadline', 'idx_deadline');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_task_items');
    }
};
