<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_milestones')) {
            Schema::create('wt_milestones', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->nullable();
                $table->unsignedInteger('project_id');
                $table->string('name', 200);
                $table->text('description')->nullable();
                $table->string('color', 7)->nullable();
                $table->enum('status', ['planned', 'in_progress', 'completed', 'delayed', 'cancelled'])->default('planned');
                $table->date('planned_start_date')->nullable();
                $table->date('planned_end_date')->nullable();
                $table->date('actual_end_date')->nullable();
                $table->unsignedTinyInteger('progress_percentage')->default(0);
                $table->boolean('is_critical')->default(false);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index('project_id', 'idx_project_id');
                $table->index('status', 'idx_status');
                $table->index('planned_end_date', 'idx_planned_end_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_milestones');
    }
};
