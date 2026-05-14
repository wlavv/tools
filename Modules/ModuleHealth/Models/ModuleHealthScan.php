<?php

namespace Modules\ModuleHealth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleHealthScan extends Model
{
    protected $table = 'module_health_scans';

    protected $fillable = [
        'status',
        'modules_total',
        'broken_total',
        'incomplete_total',
        'functional_total',
        'enhanced_total',
        'summary',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'summary' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ModuleHealthScanItem::class, 'scan_id');
    }
}
