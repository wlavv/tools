<?php

namespace Modules\JobQueueMonitor\Entities;

use Illuminate\Database\Eloquent\Model;

class JobQueueHealthCheck extends Model
{
    protected $table = 'job_queue_monitor_health_checks';

    protected $fillable = ['check_key', 'label', 'status', 'severity', 'message', 'context', 'checked_at'];

    protected $casts = [
        'context' => 'array',
        'checked_at' => 'datetime',
    ];
}
