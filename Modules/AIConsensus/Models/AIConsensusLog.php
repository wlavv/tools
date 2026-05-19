<?php

namespace Modules\AIConsensus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIConsensusLog extends Model
{
    protected $table = 'ai_consensus_logs';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['context' => 'array', 'created_at' => 'datetime'];

    public function run(): BelongsTo
    {
        return $this->belongsTo(AIConsensusRun::class, 'run_id');
    }
}
