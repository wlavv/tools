<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class ProductIdentifier extends Model
{
    protected $table = 'wc_product_identifiers';

    protected $guarded = [];

    protected $fillable = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function store(){return $this->belongsTo(Store::class, 'id_store');}
    public function product(){return $this->belongsTo(Product::class, 'id_product');}
}
