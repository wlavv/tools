<?php

namespace Modules\ModuleComplianceCenter\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceValidator extends Model
{
    protected $table = 'module_compliance_center_validators';

    protected $fillable = [
        'validator_key',
        'name',
        'module_name',
        'service_class',
        'status',
        'is_available',
        'is_enabled',
        'is_required',
        'weight',
        'last_checked_at',
        'metadata',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'is_enabled' => 'boolean',
        'is_required' => 'boolean',
        'weight' => 'decimal:2',
        'last_checked_at' => 'datetime',
        'metadata' => 'array',
    ];
}
