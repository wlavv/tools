<?php

namespace Modules\ProductivityManager\Models;

use Illuminate\Database\Eloquent\Model;

class ProductivityTask extends Model
{
    protected $table = 'wt_productivity_tasks';

    protected $fillable = [
        'title',
        'project',
        'status',
        'priority',
        'source',
        'notes',
        'due_date',
        'blocked_reason',
        'blocked_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];
}
