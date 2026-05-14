<?php

namespace Modules\ModuleHealth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleHealthScanItem extends Model
{
    protected $table = 'module_health_scan_items';

    protected $fillable = [
        'scan_id',
        'module_name',
        'module_slug',
        'module_path',
        'profile',
        'status',
        'completion',
        'required_found',
        'required_total',
        'recommended_found',
        'recommended_total',
        'optional_found',
        'optional_total',
        'components',
        'missing_required',
        'missing_recommended',
        'present_optional',
        'recommendations',
    ];

    protected $casts = [
        'components' => 'array',
        'missing_required' => 'array',
        'missing_recommended' => 'array',
        'present_optional' => 'array',
        'recommendations' => 'array',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(ModuleHealthScan::class, 'scan_id');
    }
}
