<?php

namespace Modules\ProjectManager\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskDependency extends Model
{
    protected $table = 'wt_todo_dependencies';

    protected $fillable = [
        'task_id',
        'depends_on_task_id',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function dependency()
    {
        return $this->belongsTo(ProjectTask::class, 'depends_on_task_id');
    }
}
