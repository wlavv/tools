<?php

namespace Modules\DataImportWizard\Models;

use Illuminate\Database\Eloquent\Model;

class DataImportProfile extends Model
{
    protected $table = 'data_import_profiles';

    protected $fillable = [
        'key',
        'class_name',
        'module',
        'label',
        'status',
        'metadata',
        'last_validated_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_validated_at' => 'datetime',
    ];
}
