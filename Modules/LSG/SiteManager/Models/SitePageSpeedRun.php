<?php

namespace Modules\LSG\SiteManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SitePageSpeedRun extends Model
{
    protected $table = 'lsg_site_pagespeed_runs';

    protected $fillable = [
        'site_id',
        'checked_on',
        'strategy',
        'url',
        'status',
        'performance_score',
        'accessibility_score',
        'best_practices_score',
        'seo_score',
        'first_contentful_paint_ms',
        'largest_contentful_paint_ms',
        'total_blocking_time_ms',
        'cumulative_layout_shift',
        'speed_index_ms',
        'error_message',
        'raw_summary',
    ];

    protected $casts = [
        'checked_on' => 'date',
        'raw_summary' => 'array',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}
