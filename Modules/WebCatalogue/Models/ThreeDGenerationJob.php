<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class ThreeDGenerationJob extends Model
{
    protected $table = 'wc_3d_generation_jobs';
    protected $guarded = [];

    protected $casts = [
        'source_resource_ids' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function store(){ return $this->belongsTo(Store::class, 'id_store'); }
    public function product(){ return $this->belongsTo(Product::class, 'id_product'); }
    public function resultResource(){ return $this->belongsTo(Resource::class, 'result_resource_id'); }
    public function arResource(){ return $this->belongsTo(Resource::class, 'ar_resource_id'); }
    public function vrResource(){ return $this->belongsTo(Resource::class, 'vr_resource_id'); }
}
