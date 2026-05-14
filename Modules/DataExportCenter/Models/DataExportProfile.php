<?php

namespace Modules\DataExportCenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataExportProfile extends Model
{
    protected $table = 'data_export_profiles';

    protected $fillable = [
        'key',
        'type',
        'class_name',
        'module',
        'label',
        'description',
        'status',
        'query_sql',
        'query_bindings',
        'builder_definition',
        'default_format',
        'metadata',
        'created_by',
        'updated_by',
        'last_validated_at',
    ];

    protected $casts = [
        'query_bindings' => 'array',
        'builder_definition' => 'array',
        'metadata' => 'array',
        'last_validated_at' => 'datetime',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(DataExportBatch::class, 'profile_key', 'key');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
