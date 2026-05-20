<?php

namespace Modules\ModuleComplianceCenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceRunResult extends Model
{
    protected $table = 'module_compliance_center_run_results';

    protected $fillable = [
        'run_id',
        'run_validator_id',
        'validator_key',
        'area',
        'code',
        'status',
        'severity',
        'title',
        'message',
        'file_path',
        'line_number',
        'expected_value',
        'actual_value',
        'recommendation',
        'auto_fix_available',
        'metadata',
    ];

    protected $casts = [
        'auto_fix_available' => 'boolean',
        'metadata' => 'array',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ComplianceRun::class, 'run_id');
    }

    public function runValidator(): BelongsTo
    {
        return $this->belongsTo(ComplianceRunValidator::class, 'run_validator_id');
    }
}
