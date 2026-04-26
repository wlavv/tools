<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wt_todo_dependencies')) {
            Schema::create('wt_todo_dependencies', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('todo_id');
                $table->unsignedInteger('depends_on_todo_id');
                $table->timestamps();
                $table->unique(['todo_id', 'depends_on_todo_id'], 'wt_todo_dependencies_unique');
                $table->index('todo_id', 'wt_todo_dependencies_todo_idx');
                $table->index('depends_on_todo_id', 'wt_todo_dependencies_depends_idx');
            });

            return;
        }

        Schema::table('wt_todo_dependencies', function (Blueprint $table) {
            if (! Schema::hasColumn('wt_todo_dependencies', 'todo_id')) {
                $table->unsignedInteger('todo_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('wt_todo_dependencies', 'depends_on_todo_id')) {
                $table->unsignedInteger('depends_on_todo_id')->nullable()->after('todo_id');
            }

            if (! Schema::hasColumn('wt_todo_dependencies', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! Schema::hasColumn('wt_todo_dependencies', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });

        $columns = Schema::getColumnListing('wt_todo_dependencies');

        $legacyTodoColumn = collect(['id_todo', 'task_id', 'id_task', 'todo', 'id_parent_todo'])
            ->first(fn ($column) => in_array($column, $columns, true));

        $legacyDependsColumn = collect(['id_dependency', 'dependency_id', 'depends_on_id', 'id_depends_on_todo', 'depends_on_todo'])
            ->first(fn ($column) => in_array($column, $columns, true));

        if ($legacyTodoColumn && $legacyDependsColumn) {
            DB::table('wt_todo_dependencies')
                ->whereNull('todo_id')
                ->update(['todo_id' => DB::raw('`' . str_replace('`', '``', $legacyTodoColumn) . '`')]);

            DB::table('wt_todo_dependencies')
                ->whereNull('depends_on_todo_id')
                ->update(['depends_on_todo_id' => DB::raw('`' . str_replace('`', '``', $legacyDependsColumn) . '`')]);
        }

        DB::table('wt_todo_dependencies')
            ->where(function ($query) {
                $query->whereNull('todo_id')
                    ->orWhereNull('depends_on_todo_id')
                    ->orWhereColumn('todo_id', 'depends_on_todo_id');
            })
            ->delete();

        try {
            Schema::table('wt_todo_dependencies', function (Blueprint $table) {
                $table->unique(['todo_id', 'depends_on_todo_id'], 'wt_todo_dependencies_unique');
            });
        } catch (Throwable $e) {
            // Index may already exist under a different name in some installations.
        }

        try {
            Schema::table('wt_todo_dependencies', function (Blueprint $table) {
                $table->index('todo_id', 'wt_todo_dependencies_todo_idx');
                $table->index('depends_on_todo_id', 'wt_todo_dependencies_depends_idx');
            });
        } catch (Throwable $e) {
            // Index may already exist under a different name in some installations.
        }
    }

    public function down(): void
    {
        // Do not drop compatibility columns automatically: this table may contain production data.
    }
};
