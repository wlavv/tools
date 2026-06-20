<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductAsset extends Model
{
    protected $table = 'lsg_catalog_product_assets';
    protected $fillable = [
        'product_id','store_product_id','store_id','asset_type','asset_role','source_module','source_id','title','file_path','public_url',
        'mime_type','extension','language','is_public','is_primary','is_syncable_to_prestashop','is_syncable_to_webcatalogue',
        'approval_status','brand_compliance_status','quality_score','sort_order','created_by','approved_by','approved_at','metadata'
    ];
    protected $casts = [
        'is_public' => 'boolean','is_primary' => 'boolean','is_syncable_to_prestashop' => 'boolean','is_syncable_to_webcatalogue' => 'boolean',
        'quality_score' => 'decimal:2','approved_at' => 'datetime','metadata' => 'array'
    ];

    public function product(): BelongsTo { return $this->belongsTo(Product::class, 'product_id'); }
    public function storeProduct(): BelongsTo { return $this->belongsTo(StoreProduct::class, 'store_product_id'); }
    public function store(): BelongsTo { return $this->belongsTo(ProductStore::class, 'store_id'); }
}
