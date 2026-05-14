<?php

namespace Modules\ModuleDependencyMap\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleDependencyScan extends Model
{
    public const STATUS_RUNNING = 'running';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    public const HEALTH_UNKNOWN = 'unknown';
    public const HEALTH_HEALTHY = 'healthy';
    public const HEALTH_WARNING = 'warning';
    public const HEALTH_RISKY = 'risky';
    public const HEALTH_CRITICAL = 'critical';

    protected $table = 'module_dependency_scans';

    protected $fillable = [
        'module_name',
        'status',
        'health_status',
        'risk_score',
        'direct_dependencies_count',
        'dependents_count',
        'circular_dependencies_count',
        'critical_dependencies_count',
        'stale_dependencies_count',
        'started_at',
        'finished_at',
        'triggered_by',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'risk_score' => 'integer',
        'direct_dependencies_count' => 'integer',
        'dependents_count' => 'integer',
        'circular_dependencies_count' => 'integer',
        'critical_dependencies_count' => 'integer',
        'stale_dependencies_count' => 'integer',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    public function dependencies(): HasMany
    {
        return $this->hasMany(ModuleDependency::class, 'latest_scan_id');
    }
}
