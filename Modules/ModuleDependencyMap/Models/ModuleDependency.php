<?php

namespace Modules\ModuleDependencyMap\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleDependency extends Model
{
    protected $table = 'module_dependencies';

    protected $fillable = [
        'source_module',
        'target_module',
        'dependency_type',
        'file_path',
        'line_number',
        'reference',
        'confidence',
        'evidence_hash',
        'is_active',
        'first_detected_at',
        'last_detected_at',
        'latest_scan_id',
    ];

    protected $casts = [
        'line_number' => 'integer',
        'confidence' => 'integer',
        'is_active' => 'boolean',
        'first_detected_at' => 'datetime',
        'last_detected_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function latestScan(): BelongsTo
    {
        return $this->belongsTo(ModuleDependencyScan::class, 'latest_scan_id');
    }
}
