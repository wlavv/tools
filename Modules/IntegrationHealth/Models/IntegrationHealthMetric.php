<?php

namespace Modules\IntegrationHealth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntegrationHealthMetric extends Model
{
    protected $table = 'integration_health_metrics';

    protected $fillable = [
        'service_id', 'service_slug', 'metric', 'value', 'unit', 'payload', 'recorded_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'recorded_at' => 'datetime',
        'value' => 'decimal:4',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(IntegrationHealthService::class, 'service_id');
    }
}
