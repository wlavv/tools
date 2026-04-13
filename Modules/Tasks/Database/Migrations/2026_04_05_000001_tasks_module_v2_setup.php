<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wt_task_members')) {
            Schema::create('wt_task_members', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->unsignedTinyInteger('task_type')->default(1);
                $table->string('color', 20)->nullable();
                $table->integer('sort_order')->nullable();
                $table->boolean('is_active')->default(1);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('wt_tasks')) {
            Schema::table('wt_tasks', function (Blueprint $table) {
                if (! Schema::hasColumn('wt_tasks', 'member_id')) {
                    $table->unsignedBigInteger('member_id')->nullable()->after('id');
                }
                if (! Schema::hasColumn('wt_tasks', 'sort_order')) {
                    $table->integer('sort_order')->nullable()->after('value');
                }
                if (! Schema::hasColumn('wt_tasks', 'is_active')) {
                    $table->boolean('is_active')->default(1)->after('sort_order');
                }
                if (! Schema::hasColumn('wt_tasks', 'days_mask')) {
                    $table->string('days_mask', 50)->nullable()->after('is_active');
                }
            });
        }

        if (Schema::hasTable('wt_tasks_done')) {
            Schema::table('wt_tasks_done', function (Blueprint $table) {
                if (! Schema::hasColumn('wt_tasks_done', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (! Schema::hasColumn('wt_tasks_done', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('wt_task_members') && Schema::hasTable('wt_tasks')) {
            $members = [
                ['name' => 'Márcia', 'slug' => 'marcia', 'task_type' => 1, 'color' => '#0d6efd', 'sort_order' => 10, 'is_active' => 1],
                ['name' => 'Bruno', 'slug' => 'bruno', 'task_type' => 1, 'color' => '#198754', 'sort_order' => 20, 'is_active' => 1],
                ['name' => 'Inês', 'slug' => 'ines', 'task_type' => 2, 'color' => '#6f42c1', 'sort_order' => 30, 'is_active' => 1],
                ['name' => 'Eva', 'slug' => 'eva', 'task_type' => 2, 'color' => '#fd7e14', 'sort_order' => 40, 'is_active' => 1],
            ];

            foreach ($members as $member) {
                $exists = DB::table('wt_task_members')->where('name', $member['name'])->exists();
                if (! $exists) {
                    DB::table('wt_task_members')->insert(array_merge($member, [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]));
                }
            }

            $allMembers = DB::table('wt_task_members')->get()->keyBy('name');
            $tasks = DB::table('wt_tasks')->get(['id', 'name', 'type', 'member_id']);

            foreach ($tasks as $task) {
                $member = $allMembers[$task->name] ?? null;
                if ($member && empty($task->member_id)) {
                    DB::table('wt_tasks')->where('id', $task->id)->update([
                        'member_id' => $member->id,
                        'type' => $member->task_type,
                        'is_active' => DB::raw('COALESCE(is_active, 1)'),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('wt_tasks')) {
            Schema::table('wt_tasks', function (Blueprint $table) {
                if (Schema::hasColumn('wt_tasks', 'days_mask')) {
                    $table->dropColumn('days_mask');
                }
                if (Schema::hasColumn('wt_tasks', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if (Schema::hasColumn('wt_tasks', 'sort_order')) {
                    $table->dropColumn('sort_order');
                }
                if (Schema::hasColumn('wt_tasks', 'member_id')) {
                    $table->dropColumn('member_id');
                }
            });
        }

        if (Schema::hasTable('wt_task_members')) {
            Schema::dropIfExists('wt_task_members');
        }
    }
};
