<?php

namespace Modules\ErrorCenter\Models;

use Illuminate\Database\Eloquent\Model;

class ErrorEventUser extends Model
{
    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'error_event_users';

    protected $fillable = [
        'error_event_id',
        'user_id',
        'first_seen_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
    ];
}
