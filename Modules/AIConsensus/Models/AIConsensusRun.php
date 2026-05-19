<?php

namespace Modules\AIConsensus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AIConsensusRun extends Model
{
    protected $table = 'ai_consensus_runs';
    protected $guarded = ['id'];

    protected $casts = [
        'input_payload' => 'array',
        'context_payload' => 'array',
        'options' => 'array',
        'final_score' => 'decimal:2',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(AIConsensusTemplate::class, 'template_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AIConsensusMessage::class, 'run_id');
    }

    public function providerResponses(): HasMany
    {
        return $this->hasMany(AIConsensusProviderResponse::class, 'run_id');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(AIConsensusOutput::class, 'run_id');
    }

    public function contexts(): HasMany
    {
        return $this->hasMany(AIConsensusContext::class, 'run_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AIConsensusLog::class, 'run_id');
    }
}
