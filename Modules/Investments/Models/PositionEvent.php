<?php

namespace Modules\Investments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PositionEvent extends Model
{
    protected $table = 'wt_investments_position_events';

    protected $fillable = [
        'position_id',
        'type',
        'price',
        'data',
        'event_time',
    ];

    protected $casts = [
        'data' => 'array',
        'event_time' => 'datetime',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
