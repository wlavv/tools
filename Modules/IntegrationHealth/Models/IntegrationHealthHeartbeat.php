<?php

namespace Modules\IntegrationHealth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationHealthHeartbeat extends Model
{
    protected $table = 'integration_health_heartbeats';

    protected $fillable = [
        'service_id', 'service_slug', 'heartbeat_at', 'response_time_ms', 'status', 'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'heartbeat_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(IntegrationHealthService::class, 'service_id');
    }
}
