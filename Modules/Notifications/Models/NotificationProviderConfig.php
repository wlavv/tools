<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationProviderConfig extends Model
{
    use HasFactory;

    protected $table = 'notification_provider_configs';

    protected $fillable = [
        'channel', 'provider', 'enabled', 'settings',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'settings' => 'array',
    ];
}
