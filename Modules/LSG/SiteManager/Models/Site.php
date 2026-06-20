<?php

namespace Modules\LSG\SiteManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Site extends Model
{
    use SoftDeletes;

    protected $table = 'lsg_sites';

    protected $fillable = [
        'name',
        'slug',
        'site_type',
        'domain',
        'public_url',
        'environment',
        'status',
        'default_language',
        'default_currency',
        'project_id',
        'monitor_pagespeed',
        'monitor_availability',
        'settings',
        'notes',
    ];

    protected $casts = [
        'monitor_pagespeed' => 'boolean',
        'monitor_availability' => 'boolean',
        'settings' => 'array',
    ];

    public function pageSpeedRuns(): HasMany
    {
        return $this->hasMany(SitePageSpeedRun::class, 'site_id');
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(SiteIntegration::class, 'site_id');
    }

    public function getResolvedUrlAttribute(): ?string
    {
        $url = trim((string) ($this->public_url ?: $this->domain));

        if ($url === '') {
            return null;
        }

        return str_starts_with($url, 'http://') || str_starts_with($url, 'https://')
            ? $url
            : 'https://' . $url;
    }
}
