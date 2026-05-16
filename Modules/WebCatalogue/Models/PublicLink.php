<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PublicLink extends Model
{
    protected $table = 'wc_public_links';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'raw_payload' => 'array',
        'vr_scene_config' => 'array',
        'ar_scene_config' => 'array',
        'is_default' => 'boolean',
        'is_featured' => 'boolean',
        'is_main' => 'boolean',
        'show_prices' => 'boolean',
        'show_promotions' => 'boolean',
        'published_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function store(){return $this->belongsTo(Store::class, 'id_store');}
    public function catalogue(){return $this->belongsTo(Catalogue::class, 'id_catalogue');}
    public function product(){return $this->belongsTo(Product::class, 'id_product');}

    public function scopeUsable(Builder $query): Builder
    {
        return $query->where(function (Builder $query) {
            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }

    public function getTrackedViewsAttribute(): int
    {
        return (int) data_get($this->metadata, 'tracking.views', 0);
    }

}
