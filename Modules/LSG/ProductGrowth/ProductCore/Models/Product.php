<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\CatalogManager\Models\CatalogManufacturer;
use Modules\CatalogManager\Models\CatalogSupplier;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'lsg_catalog_core_products';
    protected $fillable = [
        'internal_sku','reference','ean','mpn','brand_id','supplier_id','name','description','product_type',
        'base_cost','base_price','weight','width','height','depth','status','data_quality_score','is_active',
        'metadata','created_by','updated_by'
    ];
    protected $casts = [
        'base_cost' => 'decimal:2','base_price' => 'decimal:2','weight' => 'decimal:3','width' => 'decimal:3',
        'height' => 'decimal:3','depth' => 'decimal:3','data_quality_score' => 'decimal:2','is_active' => 'boolean','metadata' => 'array'
    ];

    public function brand(): BelongsTo { return $this->belongsTo(CatalogManufacturer::class, 'brand_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(CatalogSupplier::class, 'supplier_id'); }
    public function storeProducts(): HasMany { return $this->hasMany(StoreProduct::class, 'product_id'); }
    public function assets(): HasMany { return $this->hasMany(ProductAsset::class, 'product_id'); }
    public function productAttributes(): HasMany { return $this->hasMany(ProductAttributeValue::class, 'product_id'); }
    public function productCharacteristics(): HasMany { return $this->hasMany(ProductCharacteristicValue::class, 'product_id'); }

    public function getPrimaryAssetAttribute(): ?ProductAsset
    {
        return $this->assets->firstWhere('is_primary', true) ?: $this->assets->first();
    }

    public function getMasterManufacturerNameAttribute(): ?string
    {
        return data_get($this->metadata ?? [], 'product_growth.manufacturer_name') ?: $this->brand?->name;
    }

    public function getMasterSupplierNameAttribute(): ?string
    {
        return data_get($this->metadata ?? [], 'product_growth.supplier_name') ?: $this->supplier?->name;
    }
}
