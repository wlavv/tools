<?php

namespace Modules\WebCatalogue\Models;

use Illuminate\Database\Eloquent\Model;

class SessionLog extends Model
{
    protected $table = 'wc_session_logs';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];
}
