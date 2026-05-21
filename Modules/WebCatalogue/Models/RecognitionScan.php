<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class RecognitionScan extends Model
{
    protected $table = 'wc_recognition_scans';

    protected $guarded = [];

    protected $casts = [
        'top_3_candidates' => 'array',
        'comparator_scores' => 'array',
        'metadata' => 'array',
        'top_1_correct' => 'boolean',
        'top_3_correct' => 'boolean',
        'false_positive' => 'boolean',
        'false_negative' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function session(){ return $this->belongsTo(VisualRecognitionSession::class, 'id_session'); }
    public function capture(){ return $this->belongsTo(VisualRecognitionCapture::class, 'id_capture'); }
    public function store(){ return $this->belongsTo(Store::class, 'id_store'); }
    public function catalogue(){ return $this->belongsTo(Catalogue::class, 'id_catalogue'); }
    public function topProduct(){ return $this->belongsTo(Product::class, 'top_1_product_id'); }
    public function candidates(){ return $this->hasMany(RecognitionScanCandidate::class, 'id_scan'); }
    public function timings(){ return $this->hasOne(RecognitionScanTiming::class, 'id_scan'); }
}
