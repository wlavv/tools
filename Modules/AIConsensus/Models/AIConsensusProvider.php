<?php

namespace Modules\AIConsensus\Models;

use Illuminate\Database\Eloquent\Model;

class AIConsensusProvider extends Model
{
    protected $table = 'ai_consensus_providers';
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'weight' => 'decimal:4',
        'config' => 'array',
    ];
}
