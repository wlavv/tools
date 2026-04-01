<?php

namespace Modules\ProductivityManager\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityAlert extends Model
{
    protected $table = 'wt_productivity_alerts';

    protected $fillable = [
        'title',
        'severity',
        'source',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
