<?php

namespace Modules\ProjectManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTask extends Model
{
    public const STATUS_PENDING = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_BLOCKED = 2;
    public const STATUS_DONE = 3;
    public const STATUS_CANCELLED = 4;

    protected $table = 'wt_todo';

    protected $fillable = [
        'id_project',
        'owner_id',
        'title',
        'priority',
        'execution_order',
        'status',
        'start_date',
        'scheduled_for',
        'deadline',
        'expected_time',
        'id_parent',
        'comment',
        'type',
        'description',
        'source',
        'blocked_reason',
        'completed_at',
    ];

    protected $casts = [
        'priority' => 'integer',
        'execution_order' => 'integer',
        'status' => 'integer',
        'expected_time' => 'integer',
        'id_parent' => 'integer',
        'start_date' => 'datetime',
        'scheduled_for' => 'datetime',
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectManager::class, 'id_project');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'id_parent');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'id_parent')->orderBy('execution_order')->orderBy('priority');
    }

    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'wt_todo_dependencies',
            'task_id',
            'depends_on_task_id'
        )->withTimestamps();
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'wt_todo_dependencies',
            'depends_on_task_id',
            'task_id'
        )->withTimestamps();
    }

    public function getStatusLabelAttribute(): string
    {
        return config('project-manager.task_statuses.' . $this->status, 'Unknown');
    }

    public function getPriorityLabelAttribute(): string
    {
        return config('project-manager.task_priorities.' . $this->priority, 'Normal');
    }

    public function getIsDoneAttribute(): bool
    {
        return (int) $this->status === self::STATUS_DONE;
    }

    public function getIsBlockedAttribute(): bool
    {
        return (int) $this->status === self::STATUS_BLOCKED;
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [self::STATUS_DONE, self::STATUS_CANCELLED]);
    }

    public function scopeDone($query)
    {
        return $query->where('status', self::STATUS_DONE);
    }

    public function scopeExecutable($query)
    {
        return $query->open()->whereDoesntHave('dependencies', function ($dependencyQuery) {
            $dependencyQuery->whereNotIn('status', [self::STATUS_DONE, self::STATUS_CANCELLED]);
        });
    }
}
