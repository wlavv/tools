<?php

namespace App\Modules\AiConsensus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiRun extends Model
{
    protected $table = 'ai_runs';

    protected $fillable = [
        'created_by',
        'title',
        'template_key',
        'prompt',
        'status',
        'final_answer',
        'final_provider',
        'final_model',
        'total_tokens_in',
        'total_tokens_out',
        'total_cost_estimate_usd',
        'options',
        'meta',
    ];

    protected $casts = [
        'options' => 'array',
        'meta' => 'array',
    ];

    public function responses(): HasMany
    {
        return $this->hasMany(AiRunResponse::class, 'ai_run_id');
    }
}