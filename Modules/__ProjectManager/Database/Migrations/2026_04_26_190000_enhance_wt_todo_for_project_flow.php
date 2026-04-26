<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('wt_todo')) {
            Schema::table('wt_todo', function (Blueprint $table) {
                if (!Schema::hasColumn('wt_todo', 'type')) {
                    $table->string('type', 40)->nullable()->after('comment');
                }

                if (!Schema::hasColumn('wt_todo', 'description')) {
                    $table->text('description')->nullable()->after('type');
                }

                if (!Schema::hasColumn('wt_todo', 'execution_order')) {
                    $table->unsignedInteger('execution_order')->default(0)->after('priority');
                }

                if (!Schema::hasColumn('wt_todo', 'scheduled_for')) {
                    $table->dateTime('scheduled_for')->nullable()->after('start_date');
                }

                if (!Schema::hasColumn('wt_todo', 'deadline')) {
                    $table->dateTime('deadline')->nullable()->after('scheduled_for');
                }

                if (!Schema::hasColumn('wt_todo', 'completed_at')) {
                    $table->dateTime('completed_at')->nullable()->after('deadline');
                }

                if (!Schema::hasColumn('wt_todo', 'owner_id')) {
                    $table->unsignedBigInteger('owner_id')->nullable()->after('id_project');
                }

                if (!Schema::hasColumn('wt_todo', 'source')) {
                    $table->string('source', 40)->default('manual')->after('description');
                }

                if (!Schema::hasColumn('wt_todo', 'blocked_reason')) {
                    $table->string('blocked_reason', 255)->nullable()->after('source');
                }
            });
        }

        if (!Schema::hasTable('wt_todo_dependencies')) {
            Schema::create('wt_todo_dependencies', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('task_id');
                $table->unsignedInteger('depends_on_task_id');
                $table->timestamps();

                $table->unique(['task_id', 'depends_on_task_id'], 'wt_todo_dep_unique');
                $table->index('task_id', 'wt_todo_dep_task_idx');
                $table->index('depends_on_task_id', 'wt_todo_dep_depends_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_todo_dependencies');

        if (Schema::hasTable('wt_todo')) {
            Schema::table('wt_todo', function (Blueprint $table) {
                foreach ([
                    'blocked_reason',
                    'source',
                    'owner_id',
                    'completed_at',
                    'deadline',
                    'scheduled_for',
                    'execution_order',
                    'description',
                    'type',
                ] as $column) {
                    if (Schema::hasColumn('wt_todo', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
