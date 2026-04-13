<?php

namespace Modules\Notifications\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'uuid', 'title', 'message', 'type', 'category', 'priority', 'status', 'icon',
        'action_label', 'action_url', 'source_module', 'reference_type', 'reference_id',
        'created_by', 'meta', 'scheduled_at', 'expires_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'scheduled_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function recipients()
    {
        return $this->hasMany(NotificationRecipient::class, 'notification_id');
    }

    public function logs()
    {
        return $this->hasMany(NotificationChannelLog::class, 'notification_id');
    }
}
