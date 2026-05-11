<?php

namespace Modules\Investments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $table = 'wt_investments_assets';

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
