<?php

namespace Modules\JobQueueMonitor\Entities;

use Illuminate\Database\Eloquent\Model;

class JobQueueRun extends Model
{
    protected $table = 'job_queue_monitor_runs';

    protected $fillable = [
        'uuid', 'connection', 'queue', 'job_name', 'status', 'attempts', 'duration_ms',
        'payload', 'exception_message', 'exception_file', 'exception_line', 'exception_trace',
        'started_at', 'finished_at', 'failed_at', 'resolved_at', 'resolved_by', 'resolution_note',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'failed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function scopeOpenFailures($query)
    {
        return $query->where('status', 'failed')->whereNull('resolved_at');
    }
}
