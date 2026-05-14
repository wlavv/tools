<?php

namespace Modules\IntegrationHealth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IntegrationHealthService extends Model
{
    protected $table = 'integration_health_services';

    protected $fillable = [
        'slug', 'name', 'type', 'status', 'health_score', 'avg_response_time_ms',
        'error_rate', 'last_seen_at', 'last_success_at', 'last_error_at',
        'last_error_message', 'meta', 'is_enabled',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_enabled' => 'boolean',
        'last_seen_at' => 'datetime',
        'last_success_at' => 'datetime',
        'last_error_at' => 'datetime',
        'error_rate' => 'decimal:2',
    ];

    public function events(): HasMany
    {
        return $this->hasMany(IntegrationHealthEvent::class, 'service_id');
    }

    public function heartbeats(): HasMany
    {
        return $this->hasMany(IntegrationHealthHeartbeat::class, 'service_id');
    }

    public function metrics(): HasMany
    {
        return $this->hasMany(IntegrationHealthMetric::class, 'service_id');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'online' => 'success',
            'degraded' => 'warning',
            'offline' => 'danger',
            default => 'secondary',
        };
    }
}
