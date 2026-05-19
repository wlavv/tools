<?php

namespace Modules\AIConsensus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIConsensusContext extends Model
{
    protected $table = 'ai_consensus_contexts';
    protected $guarded = ['id'];
    protected $casts = ['payload' => 'array'];

    public function run(): BelongsTo
    {
        return $this->belongsTo(AIConsensusRun::class, 'run_id');
    }
}
