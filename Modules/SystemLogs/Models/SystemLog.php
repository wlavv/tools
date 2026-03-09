<?php

namespace Modules\SystemLogs\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $table = 'wt_system_logs';

    protected $fillable = [
        'level',
        'message',
        'context',
        'user_id'
    ];
}
