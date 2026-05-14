<?php

namespace Modules\ErrorCenter\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ErrorEvent extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_ACKNOWLEDGED = 'acknowledged';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_IGNORED = 'ignored';

    public const SEVERITY_CRITICAL = 'critical';
    public const SEVERITY_ERROR = 'error';
    public const SEVERITY_WARNING = 'warning';
    public const SEVERITY_INFO = 'info';

    protected $table = 'error_events';

    protected $fillable = [
        'hash',
        'title',
        'message',
        'error_type',
        'severity',
        'status',
        'module',
        'source',
        'environment',
        'first_seen_at',
        'last_seen_at',
        'occurrence_count',
        'affected_users_count',
        'assigned_to',
        'resolved_at',
        'resolved_by',
        'last_notification_sent_at',
        'notification_count',
        'last_notification_event',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'resolved_at' => 'datetime',
        'last_notification_sent_at' => 'datetime',
        'occurrence_count' => 'integer',
        'affected_users_count' => 'integer',
        'notification_count' => 'integer',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_ACKNOWLEDGED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_RESOLVED,
            self::STATUS_IGNORED,
        ];
    }

    public static function severities(): array
    {
        return [
            self::SEVERITY_CRITICAL,
            self::SEVERITY_ERROR,
            self::SEVERITY_WARNING,
            self::SEVERITY_INFO,
        ];
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(ErrorOccurrence::class, 'error_event_id');
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereNotIn('status', [self::STATUS_RESOLVED, self::STATUS_IGNORED]);
    }
}
