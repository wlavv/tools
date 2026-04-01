<?php

namespace Modules\RoadmapManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskItem extends Model
{
    use SoftDeletes;

    protected $table = 'wt_task_items';

    protected $fillable = [
        'uuid','parent_id','project_id','milestone_id','level','path','depth','code','title','description',
        'status','priority','progress_percentage','auto_progress','planned_start_date','planned_end_date',
        'actual_start_date','actual_end_date','deadline','estimated_hours','logged_hours','remaining_hours',
        'assigned_to','assigned_team','created_by','reviewed_by','risk_level','risk_notes','is_milestone_marker',
        'is_recurring','recurrence_rule','tags','custom_fields','sort_order'
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'deadline' => 'date',
        'auto_progress' => 'boolean',
        'is_milestone_marker' => 'boolean',
        'is_recurring' => 'boolean',
        'tags' => 'array',
        'custom_fields' => 'array',
    ];

    public function project() { return $this->belongsTo(Project::class, 'project_id'); }
    public function milestone() { return $this->belongsTo(Milestone::class, 'milestone_id'); }
    public function parent() { return $this->belongsTo(self::class, 'parent_id'); }
    public function children() { return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id'); }
    public function comments() { return $this->hasMany(TaskComment::class, 'task_id'); }
    public function timeLogs() { return $this->hasMany(TaskTimeLog::class, 'task_id'); }
    public function attachments() { return $this->hasMany(TaskAttachment::class, 'task_id'); }

    public function dependencies()
    {
        return $this->belongsToMany(self::class, 'wt_task_dependencies', 'task_id', 'depends_on_id');
    }
}
