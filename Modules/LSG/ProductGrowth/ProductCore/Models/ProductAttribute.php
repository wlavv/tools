<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\LSG\ProductGrowth\ProductCore\Models\Concerns\HasSlug;

class ProductAttribute extends Model
{
    use HasSlug;

    protected $table = 'lsg_catalog_core_attributes';
    protected $fillable = ['name','slug','data_type','unit','is_filterable','is_syncable','is_active'];
    protected $casts = ['is_filterable' => 'boolean','is_syncable' => 'boolean','is_active' => 'boolean'];

    public function values(): HasMany { return $this->hasMany(ProductAttributeValue::class, 'attribute_id'); }
}
