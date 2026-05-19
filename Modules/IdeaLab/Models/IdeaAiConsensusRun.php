<?php

namespace Modules\IdeaLab\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AIConsensus\Models\AIConsensusRun;

class IdeaAiConsensusRun extends Model
{
    protected $table = 'idealab_idea_ai_runs';
    protected $guarded = ['id'];

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class, 'idea_id');
    }

    public function aiConsensusRun(): BelongsTo
    {
        return $this->belongsTo(AIConsensusRun::class, 'ai_consensus_run_id');
    }
}
