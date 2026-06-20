<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class RecognitionBenchmarkRun extends Model
{
    protected $table = 'wc_recognition_benchmark_runs';
    protected $guarded = [];

    protected $casts = [
        'summary' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function session(){ return $this->belongsTo(VisualRecognitionSession::class, 'id_session'); }
    public function capture(){ return $this->belongsTo(VisualRecognitionCapture::class, 'id_capture'); }
    public function store(){ return $this->belongsTo(Store::class, 'id_store'); }
    public function results(){ return $this->hasMany(RecognitionBenchmarkResult::class, 'id_run'); }
}
