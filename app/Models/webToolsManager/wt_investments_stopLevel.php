<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class wt_investments_stopLevel extends Model
{
    protected $fillable = [
        'position_id',
        'step_index',
        'stop_loss',
        'stop_earn',
        'activated_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
