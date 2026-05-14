<?php

namespace Modules\AuditLogCentral\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLogRelation extends Model
{
    protected $fillable = ['audit_log_id', 'related_type', 'related_id', 'label'];

    public function auditLog(): BelongsTo
    {
        return $this->belongsTo(AuditLog::class);
    }
}
