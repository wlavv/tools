<?php

namespace Modules\PackageTracker\Models;

use Illuminate\Database\Eloquent\Model;

class WebhookEndpoint extends Model
{
    protected $table = 'package_tracker_webhooks';

    protected $fillable = ['name', 'url', 'secret', 'events', 'is_active', 'last_called_at', 'last_status_code'];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
        'last_called_at' => 'datetime',
    ];

    protected $hidden = ['secret'];
}
