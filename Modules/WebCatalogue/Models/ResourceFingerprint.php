<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceFingerprint extends Model
{
    protected $table = 'wc_resource_fingerprints';

    protected $guarded = [];

    protected $casts = [
        'vector_json' => 'array',
        'metadata' => 'array',
    ];

    public function store(){return $this->belongsTo(Store::class, 'id_store');}
    public function product(){return $this->belongsTo(Product::class, 'id_product');}
    public function resource(){return $this->belongsTo(Resource::class, 'id_resource');}
    public function fullProfile(){return $this->hasOne(ResourceFingerprintProfile::class, 'id_fingerprint');}
}
