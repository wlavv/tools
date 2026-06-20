<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCategoryCharacteristic extends Model
{
    protected $table = 'lsg_catalog_category_characteristics';

    protected $fillable = [
        'store_category_id',
        'characteristic_id',
        'is_required',
        'position',
        'section',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'position' => 'integer',
    ];

    public function characteristic(): BelongsTo
    {
        return $this->belongsTo(ProductCharacteristic::class, 'characteristic_id');
    }
}
