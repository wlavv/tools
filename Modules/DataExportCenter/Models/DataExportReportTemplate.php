<?php

namespace Modules\DataExportCenter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataExportReportTemplate extends Model
{
    protected $table = 'data_export_report_templates';

    protected $fillable = [
        'key',
        'profile_key',
        'name',
        'scope_type',
        'scope_key',
        'is_default',
        'engine',
        'title_template',
        'header_html',
        'footer_html',
        'body_html',
        'css',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'metadata' => 'array',
    ];

    public function batches(): HasMany
    {
        return $this->hasMany(DataExportBatch::class, 'report_template_id');
    }
}
