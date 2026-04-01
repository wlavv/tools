<?php

namespace Modules\RoadmapManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use SoftDeletes;

    protected $table = 'wt_milestones';

    protected $fillable = [
        'uuid','project_id','name','description','color','status','planned_start_date',
        'planned_end_date','actual_end_date','progress_percentage','is_critical','sort_order','created_by'
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_end_date' => 'date',
        'is_critical' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function tasks()
    {
        return $this->hasMany(TaskItem::class, 'milestone_id');
    }
}
