<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'wc_products';

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
    public function catalogues(){return $this->belongsToMany(Catalogue::class, 'wc_catalogue_products', 'id_product', 'id_catalogue')->withPivot(['id_store','sort_order','is_featured','status','metadata'])->withTimestamps();}
    public function resources(){return $this->hasMany(Resource::class, 'id_product');}
    public function prices(){return $this->hasMany(ProductPrice::class, 'id_product');}
    public function promotions(){return $this->belongsToMany(Promotion::class, 'wc_promotion_products', 'id_product', 'id_promotion')->withPivot(['id_store','custom_badge_label','custom_sale_price','sort_order','status','metadata'])->withTimestamps();}

    public function mainImageResource()
    {
        return $this->hasOne(Resource::class, 'id_product')
            ->whereIn('resource_type', ['image', 'gallery_image', 'thumbnail', 'cover'])
            ->where(function ($query) {
                $query->where('is_main', true)->orWhere('sort_order', 0);
            })
            ->orderByDesc('is_main')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

}
