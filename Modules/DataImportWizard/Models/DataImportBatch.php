<?php

namespace Modules\DataImportWizard\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataImportBatch extends Model
{
    protected $table = 'data_import_batches';

    protected $fillable = [
        'uuid',
        'profile_key',
        'profile_class',
        'status',
        'mode',
        'original_filename',
        'disk',
        'path',
        'total_rows',
        'valid_rows',
        'error_rows',
        'warning_rows',
        'metadata',
        'created_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function rows(): HasMany
    {
        return $this->hasMany(DataImportRow::class, 'batch_id');
    }

    public function refreshCounters(): void
    {
        $this->update([
            'total_rows' => $this->rows()->count(),
            'valid_rows' => $this->rows()->where('status', 'valid')->count(),
            'error_rows' => $this->rows()->whereIn('status', ['invalid', 'failed'])->count(),
            'warning_rows' => $this->rows()->whereJsonLength('warnings', '>', 0)->count(),
        ]);
    }
}
