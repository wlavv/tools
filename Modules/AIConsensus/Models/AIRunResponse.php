<?php

namespace Modules\AIConsensus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIRunResponse extends Model
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
        'cost_estimate_usd' => 'decimal:4',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(AIConsensus::class, 'ai_run_id');
    }
}
