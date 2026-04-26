<?php

namespace Modules\ProjectManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class ProjectTask extends Model
{
    protected $table = 'wt_todo';

    protected $fillable = [
        'id_project',
        'id_parent',
        'title',
        'description',
        'comment',
        'priority',
        'status',
        'type',
        'start_date',
        'deadline',
        'scheduled_for',
        'expected_time',
        'execution_order',
        'completed_at',
    ];

    protected $casts = [
        'id_project' => 'integer',
        'id_parent' => 'integer',
        'priority' => 'integer',
        'status' => 'integer',
        'expected_time' => 'integer',
        'execution_order' => 'integer',
        'start_date' => 'datetime',
        'deadline' => 'datetime',
        'scheduled_for' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const STATUS_TODO = 0;
    public const STATUS_READY = 1;
    public const STATUS_IN_PROGRESS = 2;
    public const STATUS_BLOCKED = 3;
    public const STATUS_DONE = 4;
    public const STATUS_CANCELLED = 5;

    public const PRIORITY_LOW = 1;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_CRITICAL = 4;

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
        return $this->hasMany(self::class, 'id_parent')
            ->orderBy('execution_order')
            ->orderByDesc('priority')
            ->orderBy('id');
    }

    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Standard dependency relation.
     *
     * This assumes the compatibility migration has created the normalized columns:
     * - wt_todo_dependencies.todo_id
     * - wt_todo_dependencies.depends_on_todo_id
     */
    public function dependencies(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'wt_todo_dependencies',
            'todo_id',
            'depends_on_todo_id'
        )->withTimestamps();
    }

    public function dependents(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'wt_todo_dependencies',
            'depends_on_todo_id',
            'todo_id'
        )->withTimestamps();
    }

    public static function dependenciesTableReady(): bool
    {
        return Schema::hasTable('wt_todo_dependencies')
            && Schema::hasColumn('wt_todo_dependencies', 'todo_id')
            && Schema::hasColumn('wt_todo_dependencies', 'depends_on_todo_id');
    }

    public function syncDependenciesSafe(array $dependencyIds): void
    {
        if (! self::dependenciesTableReady()) {
            return;
        }

        $dependencyIds = collect($dependencyIds)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === (int) $this->id)
            ->unique()
            ->values()
            ->all();

        $this->dependencies()->sync($dependencyIds);
    }

    public function getDependencyIdsAttribute(): array
    {
        if (! self::dependenciesTableReady() || ! $this->exists) {
            return [];
        }

        if ($this->relationLoaded('dependencies')) {
            return $this->dependencies->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        return $this->dependencies()->pluck('wt_todo.id')->map(fn ($id) => (int) $id)->all();
    }

    public function scopeForProject($query, int $projectId)
    {
        return $query->where('id_project', $projectId);
    }

    public function scopeRoot($query)
    {
        return $query->where(function ($q) {
            $q->where('id_parent', 0)->orWhereNull('id_parent');
        });
    }

    public function scopeOpen($query)
    {
        return $query->whereNotIn('status', [self::STATUS_DONE, self::STATUS_CANCELLED]);
    }

    public function isDone(): bool
    {
        return (int) $this->status === self::STATUS_DONE;
    }

    public function isBlockedByDependencies(): bool
    {
        if (! self::dependenciesTableReady()) {
            return false;
        }

        return $this->dependencies()->where('status', '!=', self::STATUS_DONE)->exists();
    }

    public function getProgressPercentAttribute(): int
    {
        $children = $this->childrenRecursive;

        if ($children->isEmpty()) {
            return $this->isDone() ? 100 : 0;
        }

        $flat = $this->flattenChildren($children);
        $total = $flat->count();

        if ($total === 0) {
            return $this->isDone() ? 100 : 0;
        }

        $done = $flat->filter(fn (self $task) => $task->isDone())->count();

        return (int) round(($done / $total) * 100);
    }

    protected function flattenChildren($children)
    {
        return $children->flatMap(function (self $child) {
            return collect([$child])->merge($this->flattenChildren($child->childrenRecursive));
        });
    }

    public function statusLabel(): string
    {
        return self::statusOptions()[(int) $this->status] ?? (string) $this->status;
    }

    public function priorityLabel(): string
    {
        return self::priorityOptions()[(int) $this->priority] ?? (string) $this->priority;
    }

    public function statusCssClass(): string
    {
        return match ((int) $this->status) {
            self::STATUS_READY => 'ready',
            self::STATUS_IN_PROGRESS => 'in-progress',
            self::STATUS_BLOCKED => 'blocked',
            self::STATUS_DONE => 'done',
            self::STATUS_CANCELLED => 'cancelled',
            default => 'todo',
        };
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_TODO => __('project-manager::tasks.status_todo'),
            self::STATUS_READY => __('project-manager::tasks.status_ready'),
            self::STATUS_IN_PROGRESS => __('project-manager::tasks.status_in_progress'),
            self::STATUS_BLOCKED => __('project-manager::tasks.status_blocked'),
            self::STATUS_DONE => __('project-manager::tasks.status_done'),
            self::STATUS_CANCELLED => __('project-manager::tasks.status_cancelled'),
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            self::PRIORITY_LOW => __('project-manager::tasks.priority_low'),
            self::PRIORITY_NORMAL => __('project-manager::tasks.priority_normal'),
            self::PRIORITY_HIGH => __('project-manager::tasks.priority_high'),
            self::PRIORITY_CRITICAL => __('project-manager::tasks.priority_critical'),
        ];
    }
}
