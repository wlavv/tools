<?php

namespace Modules\AIConsensus\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIFile extends Model
{
    protected $table = 'ai_files';

    protected $fillable = [
        'ai_run_id',
        'original_name',
        'stored_path',
        'mime_type',
        'size_bytes',
        'status',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(AIConsensus::class, 'ai_run_id');
    }
}
