<?php

namespace Modules\Tasks\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskRewardOverride extends Model
{
    use HasFactory;

    protected $table = 'wt_task_reward_overrides';

    protected $fillable = [
        'year',
        'month',
        'member_id',
        'threshold_percent',
        'name',
        'description',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'member_id' => 'integer',
        'threshold_percent' => 'float',
        'sort_order' => 'integer',
        'is_active' => 'integer',
    ];
}
