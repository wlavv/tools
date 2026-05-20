<?php

namespace Modules\ModuleComplianceCenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceReport extends Model
{
    protected $table = 'module_compliance_center_reports';

    protected $fillable = [
        'run_id',
        'title',
        'summary',
        'final_status',
        'final_score',
        'report_payload',
        'recommendations',
        'ai_consensus_run_id',
        'project_tasks_payload',
        'created_by',
    ];

    protected $casts = [
        'final_score' => 'decimal:2',
        'report_payload' => 'array',
        'recommendations' => 'array',
        'project_tasks_payload' => 'array',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(ComplianceRun::class, 'run_id');
    }
}
