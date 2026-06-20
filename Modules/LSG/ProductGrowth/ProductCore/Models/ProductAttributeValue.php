<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAttributeValue extends Model
{
    protected $table = 'lsg_catalog_core_product_attributes';
    protected $fillable = ['product_id','attribute_id','value','value_json'];
    protected $casts = ['value_json' => 'array'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class, 'product_id'); }
    public function attribute(): BelongsTo { return $this->belongsTo(ProductAttribute::class, 'attribute_id'); }
}
