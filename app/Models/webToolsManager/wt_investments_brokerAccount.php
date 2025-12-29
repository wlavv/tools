<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class wt_investments_brokerAccount extends Model
{
    protected $fillable = [
        'user_id',
        'broker',
        'name',
        'external_account_id',
        'currency',
        'is_demo',
        'balance',
        'settings',
    ];

    protected $casts = [
        'is_demo' => 'boolean',
        'settings' => 'array',
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
