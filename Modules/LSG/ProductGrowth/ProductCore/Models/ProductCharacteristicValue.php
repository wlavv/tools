<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCharacteristicValue extends Model
{
    protected $table = 'lsg_catalog_core_product_characteristics';
    protected $fillable = ['product_id','characteristic_id','value','value_json'];
    protected $casts = ['value_json' => 'array'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function characteristic(): BelongsTo
    {
        return $this->belongsTo(ProductCharacteristic::class, 'characteristic_id');
    }
}
