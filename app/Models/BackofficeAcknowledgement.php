<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackofficeAcknowledgement extends Model
{
    protected $table = 'backoffice_acknowledgements';

    protected $fillable = [
        'user_id',
        'source_type',
        'source_id',
        'source_hash',
        'status',
        'acknowledged_at',
        'context',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'context' => 'array',
    ];
}
