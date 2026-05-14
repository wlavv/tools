<?php

namespace Modules\ErrorCenter\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorOccurrence extends Model
{
    use HasFactory;

    protected $table = 'error_occurrences';

    protected $fillable = [
        'error_event_id',
        'occurred_at',
        'user_id',
        'tenant_id',
        'request_id',
        'correlation_id',
        'endpoint',
        'http_method',
        'status_code',
        'ip_address',
        'user_agent',
        'stack_trace',
        'payload_snapshot',
        'context_json',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'status_code' => 'integer',
        'payload_snapshot' => 'array',
        'context_json' => 'array',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(ErrorEvent::class, 'error_event_id');
    }
}
