<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class Catalogue extends Model
{
    protected $table = 'wc_catalogues';

    protected $guarded = [];

    protected $fillable = [];

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
    public function products(){return $this->belongsToMany(Product::class, 'wc_catalogue_products', 'id_catalogue', 'id_product')->withPivot(['id_store','sort_order','is_featured','status','metadata'])->withTimestamps();}

    public function coverResource()
    {
        return $this->hasOne(Resource::class, 'id_catalogue')
            ->whereIn('resource_type', ['cover', 'image', 'gallery_image', 'thumbnail'])
            ->orderByDesc('is_main')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

}
