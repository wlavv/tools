<?php

namespace Modules\AIConsensus\Models;

use Illuminate\Database\Eloquent\Model;

class AIConsensusTemplate extends Model
{
    protected $table = 'ai_consensus_templates';
    protected $guarded = ['id'];

    protected $casts = [
        'expected_output_schema' => 'array',
        'default_options' => 'array',
        'is_active' => 'boolean',
    ];
}
