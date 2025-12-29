<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class wt_investments_positionEvent extends Model
{
    protected $fillable = [
        'position_id',
        'type',
        'price',
        'data',
        'event_time',
    ];

    protected $casts = [
        'data'       => 'array',
        'event_time' => 'datetime',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
