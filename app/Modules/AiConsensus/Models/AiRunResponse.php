<?php

namespace App\Modules\AiConsensus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRunResponse extends Model
{
    protected $table = 'ai_run_responses';

    protected $fillable = [
        'ai_run_id',
        'provider',
        'model',
        'status',
        'raw_output',
        'error',
        'tokens_in',
        'tokens_out',
        'cost_estimate_usd',
        'latency_ms',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(AiRun::class, 'ai_run_id');
    }
}