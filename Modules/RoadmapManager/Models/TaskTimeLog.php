<?php

namespace Modules\RoadmapManager\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTimeLog extends Model
{
    protected $table = 'wt_task_time_logs';

    protected $fillable = ['task_id','user_id','logged_hours','log_date','description'];

    protected $casts = ['log_date' => 'date'];
}
