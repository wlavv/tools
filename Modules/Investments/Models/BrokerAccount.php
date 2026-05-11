<?php

namespace Modules\Investments\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrokerAccount extends Model
{
    protected $table = 'wt_investments_broker_accounts';

    protected $fillable = [
        'user_id',
        'broker',
        'name',
        'external_account_id',
        'currency',
        'is_demo',
        'balance',
        'settings',
        'connection_status',
        'last_sync_at',
        'connection_error',
    ];

    protected $casts = [
        'is_demo' => 'boolean',
        'balance' => 'decimal:2',
        'settings' => 'array',
        'last_sync_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
