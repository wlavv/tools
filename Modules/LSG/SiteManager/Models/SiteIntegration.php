<?php

namespace Modules\LSG\SiteManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteIntegration extends Model
{
    protected $table = 'lsg_site_integrations';

    protected $fillable = [
        'site_id',
        'integration_type',
        'name',
        'status',
        'config',
        'last_checked_at',
        'last_error',
    ];

    protected $casts = [
        'config' => 'array',
        'last_checked_at' => 'datetime',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}
