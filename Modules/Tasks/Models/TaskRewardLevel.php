<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskRewardLevel extends Model
{
    use HasFactory;

    protected $table = 'wt_task_reward_levels';

    protected $fillable = [
        'member_id',
        'threshold_percent',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'member_id' => 'integer',
        'threshold_percent' => 'float',
        'sort_order' => 'integer',
        'is_active' => 'integer',
    ];

    public static function bootstrapDefaults(): void
    {
        if (static::query()->exists()) {
            return;
        }

        $defaults = [
            ['member_id' => null, 'threshold_percent' => 50, 'name' => 'Prémio Base', 'description' => 'Atingiu o primeiro escalão do mês.', 'sort_order' => 10, 'is_active' => 1],
            ['member_id' => null, 'threshold_percent' => 75, 'name' => 'Prémio Intermédio', 'description' => 'Bom desempenho mensal.', 'sort_order' => 20, 'is_active' => 1],
            ['member_id' => null, 'threshold_percent' => 90, 'name' => 'Prémio Alto', 'description' => 'Excelente consistência durante o mês.', 'sort_order' => 30, 'is_active' => 1],
            ['member_id' => null, 'threshold_percent' => 100, 'name' => 'Prémio Máximo', 'description' => 'Todas as tarefas concluídas.', 'sort_order' => 40, 'is_active' => 1],
        ];

        foreach ($defaults as $row) {
            static::query()->create($row);
        }
    }
}
