<?php

namespace Modules\ModuleComplianceCenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComplianceManagedModule extends Model
{
    protected $table = 'module_compliance_center_modules';

    protected $fillable = [
        'module_name',
        'module_slug',
        'module_path',
        'module_version',
        'module_description',
        'manifest_payload',
        'last_run_id',
        'last_status',
        'last_score',
        'last_checked_at',
        'is_active',
    ];

    protected $casts = [
        'manifest_payload' => 'array',
        'last_score' => 'decimal:2',
        'last_checked_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function runs(): HasMany
    {
        return $this->hasMany(ComplianceRun::class, 'managed_module_id');
    }
}
