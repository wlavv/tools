<?php

namespace Modules\AIConsensus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIConsensusProviderResponse extends Model
{
    protected $table = 'ai_consensus_provider_responses';
    protected $guarded = ['id'];

    protected $casts = [
        'input_payload' => 'array',
        'normalized_response' => 'array',
        'score' => 'decimal:2',
        'cost_estimate' => 'decimal:6',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(AIConsensusRun::class, 'run_id');
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AIConsensusProvider::class, 'provider_id');
    }
}
