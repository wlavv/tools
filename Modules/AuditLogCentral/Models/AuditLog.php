<?php

namespace Modules\AuditLogCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuditLog extends Model
{
    protected $fillable = [
        'uuid', 'module', 'event', 'action', 'severity', 'status',
        'auditable_type', 'auditable_id', 'user_id', 'user_name', 'user_email',
        'ip_address', 'user_agent', 'request_method', 'request_url',
        'source', 'correlation_id', 'metadata', 'payload', 'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'payload' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function changes(): HasMany
    {
        return $this->hasMany(AuditLogChange::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(AuditLogTag::class);
    }

    public function relations(): HasMany
    {
        return $this->hasMany(AuditLogRelation::class);
    }

    public function scopeFilters($query, array $filters)
    {
        return $query
            ->when($filters['module'] ?? null, fn ($q, $v) => $q->where('module', $v))
            ->when($filters['severity'] ?? null, fn ($q, $v) => $q->where('severity', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['event'] ?? null, fn ($q, $v) => $q->where('event', 'like', "%{$v}%"))
            ->when($filters['user'] ?? null, fn ($q, $v) => $q->where(function ($s) use ($v) {
                $s->where('user_name', 'like', "%{$v}%")->orWhere('user_email', 'like', "%{$v}%");
            }))
            ->when($filters['entity_type'] ?? null, fn ($q, $v) => $q->where('auditable_type', $v))
            ->when($filters['entity_id'] ?? null, fn ($q, $v) => $q->where('auditable_id', $v))
            ->when($filters['from'] ?? null, fn ($q, $v) => $q->whereDate('occurred_at', '>=', $v))
            ->when($filters['to'] ?? null, fn ($q, $v) => $q->whereDate('occurred_at', '<=', $v));
    }
}
