<?php

namespace Modules\AIConsensus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIConsensusOutput extends Model
{
    protected $table = 'ai_consensus_outputs';
    protected $guarded = ['id'];

    protected $casts = [
        'json_payload' => 'array',
        'schema_valid' => 'boolean',
        'validation_errors' => 'array',
        'approved_at' => 'datetime',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(AIConsensusRun::class, 'run_id');
    }
}
