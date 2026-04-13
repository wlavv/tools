<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationRecipient extends Model
{
    use HasFactory;

    protected $table = 'notification_recipients';

    protected $fillable = [
        'notification_id', 'user_id', 'name', 'email', 'phone', 'discord_webhook_url',
        'delivery_channels', 'delivery_meta', 'seen_at', 'read_at', 'dismissed_at',
    ];

    protected $casts = [
        'delivery_channels' => 'array',
        'delivery_meta' => 'array',
        'seen_at' => 'datetime',
        'read_at' => 'datetime',
        'dismissed_at' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }
}
