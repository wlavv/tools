<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('wt_task_dependencies')) {
            Schema::create('wt_task_dependencies', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('task_id');
                $table->unsignedBigInteger('depends_on_id');
                $table->enum('type', ['finish_to_start','start_to_start','finish_to_finish','start_to_finish'])->default('finish_to_start');
                $table->smallInteger('lag_days')->default(0);
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamp('created_at')->nullable();

                $table->unique(['task_id', 'depends_on_id'], 'unique_dependency');
                $table->index('task_id', 'idx_task');
                $table->index('depends_on_id', 'idx_depends');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_task_dependencies');
    }
};
