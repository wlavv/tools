<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LSG\SiteManager\Models\Site;

class ProductStore extends Site
{
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

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    public function getStoreCodeAttribute(): string
    {
        return strtoupper(str_replace('-', '_', (string) $this->slug));
    }

    public function storeProducts(): HasMany { return $this->hasMany(StoreProduct::class, 'store_id'); }
}
