<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    protected $table = 'wc_stores';

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

    public function catalogues(){return $this->hasMany(Catalogue::class, 'id_store');}
    public function products(){return $this->hasMany(Product::class, 'id_store');}
    public function productIdentifiers(){return $this->hasMany(ProductIdentifier::class, 'id_store');}
    public function themes(){return $this->hasMany(StoreTheme::class, 'id_store');}
    public function environments(){return $this->hasMany(StoreEnvironment::class, 'id_store');}
    public function prices(){return $this->hasMany(ProductPrice::class, 'id_store');}
    public function promotions(){return $this->hasMany(Promotion::class, 'id_store');}
    public function publicLinks(){return $this->hasMany(PublicLink::class, 'id_store');}
    public function fingerprintRebuildLogs(){return $this->hasMany(FingerprintRebuildLog::class, 'id_store');}
    public function latestFingerprintRebuildLog(){return $this->hasOne(FingerprintRebuildLog::class, 'id_store')->latestOfMany();}

    public function resources(){return $this->hasMany(Resource::class, 'id_store');}
    public function logoResource()
    {
        return $this->hasOne(Resource::class, 'id_store')
            ->whereIn('resource_type', ['logo', 'image', 'cover'])
            ->where(function ($query) {
                $query->where('resource_owner_type', 'store')->orWhereNull('resource_owner_type');
            })
            ->orderByDesc('is_main')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

}
