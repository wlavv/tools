<?php

namespace Modules\ModuleComplianceCenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceRunValidator extends Model
{
    protected $table = 'module_compliance_center_run_validators';

    protected $fillable = [
        'run_id',
        'validator_key',
        'validator_name',
        'validator_module',
        'status',
        'score',
        'weight',
        'findings_count',
        'failed_count',
        'warning_count',
        'blocker_count',
        'started_at',
        'finished_at',
        'error_message',
        'raw_result',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'weight' => 'decimal:2',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'raw_result' => 'array',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ComplianceRun::class, 'run_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(ComplianceRunResult::class, 'run_validator_id');
    }
}
