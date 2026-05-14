<?php

namespace Modules\AuditLogCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLogTag extends Model
{
    protected $fillable = ['audit_log_id', 'tag'];

    public function auditLog(): BelongsTo
    {
        return $this->belongsTo(AuditLog::class);
    }
}
