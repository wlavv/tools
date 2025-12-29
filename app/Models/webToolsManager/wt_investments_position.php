<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class wt_investments_position extends Model
{
    protected $fillable = [
        'broker_account_id',
        'asset_id',
        'side',
        'quantity',
        'entry_price',
        'current_price',
        'initial_stop_loss',
        'initial_stop_earn',
        'current_stop_loss',
        'current_stop_earn',
        'step_value',
        'auto_manage',
        'status',
        'opened_at',
        'closed_at',
        'closed_price',
        'pnl',
        'meta',
    ];

    protected $casts = [
        'auto_manage' => 'boolean',
        'opened_at'   => 'datetime',
        'closed_at'   => 'datetime',
        'meta'        => 'array',
    ];

    public function brokerAccount(): BelongsTo
    {
        return $this->belongsTo(BrokerAccount::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function stopLevels(): HasMany
    {
        return $this->hasMany(StopLevel::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PositionEvent::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
