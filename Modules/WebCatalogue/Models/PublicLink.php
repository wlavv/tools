<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

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


}
