<?php

namespace Modules\ModuleComplianceCenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ComplianceRun extends Model
{
    protected $table = 'module_compliance_center_runs';

    protected $fillable = [
        'uuid',
        'managed_module_id',
        'module_name',
        'module_path',
        'source_type',
        'source_id',
        'status',
        'final_status',
        'structure_score',
        'design_score',
        'security_score',
        'integration_score',
        'health_score',
        'final_score',
        'total_findings',
        'failed_findings',
        'warning_findings',
        'blocker_findings',
        'options',
        'started_at',
        'finished_at',
        'error_message',
        'requested_by',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'options' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'structure_score' => 'decimal:2',
        'design_score' => 'decimal:2',
        'security_score' => 'decimal:2',
        'integration_score' => 'decimal:2',
        'health_score' => 'decimal:2',
        'final_score' => 'decimal:2',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(ComplianceManagedModule::class, 'managed_module_id');
    }

    public function validators(): HasMany
    {
        return $this->hasMany(ComplianceRunValidator::class, 'run_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ComplianceRunResult::class, 'run_id');
    }

    public function report(): HasOne
    {
        return $this->hasOne(ComplianceReport::class, 'run_id');
    }
}
