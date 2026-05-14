<?php

namespace Modules\IntegrationHealth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationHealthEvent extends Model
{
    protected $table = 'integration_health_events';

    protected $fillable = [
        'service_id', 'service_slug', 'severity', 'event_type', 'title', 'message',
        'payload', 'resolved_at', 'resolved_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(IntegrationHealthService::class, 'service_id');
    }

    public function getSeverityColorAttribute(): string
    {
        return match ($this->severity) {
            'notice' => 'info',
            'warning' => 'warning',
            'error' => 'danger',
            'critical', 'fatal' => 'dark',
            default => 'secondary',
        };
    }
}
