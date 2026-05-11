<?php

namespace Modules\Investments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StopLevel extends Model
{
    protected $table = 'wt_investments_stop_levels';

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
