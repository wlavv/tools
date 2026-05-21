<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class RecognitionScanTiming extends Model
{
    protected $table = 'wc_recognition_scan_timings';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function scan(){ return $this->belongsTo(RecognitionScan::class, 'id_scan'); }
}
