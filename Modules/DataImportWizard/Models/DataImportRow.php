<?php

namespace Modules\DataImportWizard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataImportRow extends Model
{
    protected $table = 'data_import_rows';

    protected $fillable = [
        'batch_id',
        'row_number',
        'raw_data',
        'normalized_data',
        'status',
        'errors',
        'warnings',
        'operation',
        'target_model',
        'target_id',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'normalized_data' => 'array',
        'errors' => 'array',
        'warnings' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(DataImportBatch::class, 'batch_id');
    }
}
