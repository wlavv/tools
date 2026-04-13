<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('wt_task_reward_levels')) {
            Schema::create('wt_task_reward_levels', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id')->nullable();
                $table->decimal('threshold_percent', 5, 2);
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('sort_order')->nullable();
                $table->boolean('is_active')->default(1);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('wt_task_reward_levels') && ! Schema::hasColumn('wt_task_reward_levels', 'member_id')) {
            Schema::table('wt_task_reward_levels', function (Blueprint $table) {
                $table->unsignedBigInteger('member_id')->nullable()->after('id');
            });
        }

        if (! Schema::hasTable('wt_task_reward_overrides')) {
            Schema::create('wt_task_reward_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedSmallInteger('year');
                $table->unsignedTinyInteger('month');
                $table->unsignedBigInteger('member_id')->nullable();
                $table->decimal('threshold_percent', 5, 2);
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('sort_order')->nullable();
                $table->boolean('is_active')->default(1);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('wt_task_reward_levels') && ! DB::table('wt_task_reward_levels')->exists()) {
            DB::table('wt_task_reward_levels')->insert([
                ['member_id' => null, 'threshold_percent' => 50, 'name' => 'Prémio Base', 'description' => 'Atingiu o primeiro escalão do mês.', 'sort_order' => 10, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['member_id' => null, 'threshold_percent' => 75, 'name' => 'Prémio Intermédio', 'description' => 'Bom desempenho mensal.', 'sort_order' => 20, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['member_id' => null, 'threshold_percent' => 90, 'name' => 'Prémio Alto', 'description' => 'Excelente consistência durante o mês.', 'sort_order' => 30, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
                ['member_id' => null, 'threshold_percent' => 100, 'name' => 'Prémio Máximo', 'description' => 'Todas as tarefas concluídas.', 'sort_order' => 40, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('wt_task_reward_overrides');
        Schema::dropIfExists('wt_task_reward_levels');
    }
};
