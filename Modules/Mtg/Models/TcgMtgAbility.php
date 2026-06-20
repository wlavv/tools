<?php

namespace Modules\Mtg\Models;

use Illuminate\Database\Eloquent\Model;

class TcgMtgAbility extends Model
{
    protected $table = 'tcg_mtg_abilities';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'is_official',
        'is_evergreen',
        'is_filterable',
        'is_commercial_tag',
        'description',
        'source',
        'sort_order',
        'active',
    ];

    protected $casts = [
        'is_official' => 'boolean',
        'is_evergreen' => 'boolean',
        'is_filterable' => 'boolean',
        'is_commercial_tag' => 'boolean',
        'active' => 'boolean',
    ];
}
