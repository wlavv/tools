<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class RecognitionBenchmarkResult extends Model
{
    protected $table = 'wc_recognition_benchmark_results';
    protected $guarded = [];

    protected $casts = [
        'ok' => 'boolean',
        'metrics' => 'array',
        'payload' => 'array',
    ];

    public function run(){ return $this->belongsTo(RecognitionBenchmarkRun::class, 'id_run'); }
    public function session(){ return $this->belongsTo(VisualRecognitionSession::class, 'id_session'); }
    public function capture(){ return $this->belongsTo(VisualRecognitionCapture::class, 'id_capture'); }
    public function calls(){ return $this->hasMany(RecognitionBenchmarkCall::class, 'id_result'); }
}
