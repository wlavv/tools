<?php

namespace Modules\DataExportCenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataExportBatch extends Model
{
    protected $table = 'data_export_batches';

    protected $fillable = [
        'uuid',
        'profile_key',
        'profile_type',
        'profile_class',
        'status',
        'format',
        'disk',
        'path',
        'download_name',
        'rows_count',
        'query_sql',
        'query_hash',
        'filters',
        'context',
        'report_template_id',
        'metadata',
        'errors',
        'created_by',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'context' => 'array',
        'metadata' => 'array',
        'errors' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(DataExportReportTemplate::class, 'report_template_id');
    }
}
