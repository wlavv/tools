<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationChannelLog extends Model
{
    use HasFactory;

    protected $table = 'notification_channel_logs';

    protected $fillable = [
        'notification_id', 'recipient_id', 'channel', 'provider', 'status', 'external_id',
        'request_payload', 'response_payload', 'error_message', 'sent_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'sent_at' => 'datetime',
    ];
}
