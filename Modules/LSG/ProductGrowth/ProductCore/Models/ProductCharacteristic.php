<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LSG\ProductGrowth\ProductCore\Models\Concerns\HasSlug;

class ProductCharacteristic extends Model
{
    use HasSlug;

    protected $table = 'lsg_catalog_core_characteristics';
    protected $fillable = ['name','slug','data_type','usage_scope','unit','is_filterable','is_searchable','is_seo_keyword','is_syncable','is_active'];
    protected $casts = [
        'is_filterable' => 'boolean',
        'is_searchable' => 'boolean',
        'is_seo_keyword' => 'boolean',
        'is_syncable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function values(): HasMany
    {
        return $this->hasMany(ProductCharacteristicValue::class, 'characteristic_id');
    }
}
