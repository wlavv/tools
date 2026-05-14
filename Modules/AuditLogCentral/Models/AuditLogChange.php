<?php

namespace Modules\AuditLogCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLogChange extends Model
{
    protected $fillable = ['audit_log_id', 'field', 'old_value', 'new_value', 'change_type'];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
    ];

    public function auditLog(): BelongsTo
    {
        return $this->belongsTo(AuditLog::class);
    }
}
