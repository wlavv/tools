<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class RecognitionBenchmarkCall extends Model
{
    protected $table = 'wc_recognition_benchmark_calls';
    protected $guarded = [];

    protected $casts = [
        'ok' => 'boolean',
        'headers' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function run()
    {
        return $this->belongsTo(RecognitionBenchmarkRun::class, 'id_run');
    }

    public function result()
    {
        return $this->belongsTo(RecognitionBenchmarkResult::class, 'id_result');
    }
}
