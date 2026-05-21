<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class RecognitionScanCandidate extends Model
{
    protected $table = 'wc_recognition_scan_candidates';

    protected $guarded = [];

    protected $casts = [
        'scores' => 'array',
        'metadata' => 'array',
    ];

    public function scan(){ return $this->belongsTo(RecognitionScan::class, 'id_scan'); }
    public function product(){ return $this->belongsTo(Product::class, 'id_product'); }
    public function resource(){ return $this->belongsTo(Resource::class, 'id_resource'); }
}
