<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StoreProduct extends Model
{
    protected $table = 'lsg_catalog_store_products';
    protected $fillable = [
        'product_id','store_id','name','short_description','description','seo_title','seo_description','sale_price','cost_price',
        'margin_percentage','stock_quantity','active_for_sale','sync_to_prestashop','sync_status','last_synced_at','last_sync_error',
        'internal_hash','external_hash','store_overrides'
    ];
    protected $casts = [
        'sale_price' => 'decimal:2','cost_price' => 'decimal:2','margin_percentage' => 'decimal:2',
        'active_for_sale' => 'boolean','sync_to_prestashop' => 'boolean','last_synced_at' => 'datetime','store_overrides' => 'array'
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class, 'product_id'); }
    public function store(): BelongsTo { return $this->belongsTo(ProductStore::class, 'store_id'); }
    public function assets(): HasMany { return $this->hasMany(ProductAsset::class, 'store_product_id'); }
}
