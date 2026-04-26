<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wt_todo', function (Blueprint $table) {
            if (!Schema::hasColumn('wt_todo', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            if (!Schema::hasColumn('wt_todo', 'type')) {
                $table->string('type', 50)->nullable()->after('status');
            }

            if (!Schema::hasColumn('wt_todo', 'deadline')) {
                $table->dateTime('deadline')->nullable()->after('start_date');
            }

            if (!Schema::hasColumn('wt_todo', 'scheduled_for')) {
                $table->dateTime('scheduled_for')->nullable()->after('deadline');
            }

            if (!Schema::hasColumn('wt_todo', 'execution_order')) {
                $table->integer('execution_order')->nullable()->default(0)->after('scheduled_for');
            }

            if (!Schema::hasColumn('wt_todo', 'completed_at')) {
                $table->dateTime('completed_at')->nullable()->after('updated_at');
            }
        });

        if (!Schema::hasTable('wt_todo_dependencies')) {
            Schema::create('wt_todo_dependencies', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('todo_id')->unsigned();
                $table->integer('depends_on_todo_id')->unsigned();
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();

                $table->unique(['todo_id', 'depends_on_todo_id'], 'wt_todo_dependencies_unique');
                $table->index('todo_id', 'wt_todo_dependencies_todo_idx');
                $table->index('depends_on_todo_id', 'wt_todo_dependencies_depends_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_todo_dependencies');

        Schema::table('wt_todo', function (Blueprint $table) {
            foreach (['description', 'type', 'deadline', 'scheduled_for', 'execution_order', 'completed_at'] as $column) {
                if (Schema::hasColumn('wt_todo', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
