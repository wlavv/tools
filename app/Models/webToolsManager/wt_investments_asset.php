<?php

namespace App\Models\webToolsManager;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class wt_investments_asset extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'broker',
        'external_instrument_id',
        'type',
        'exchange',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
}
