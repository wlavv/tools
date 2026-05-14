<?php

namespace Modules\PermissionRoleManager\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionAuditLog extends Model
{
    protected $table = 'permission_audit_logs';

    protected $fillable = [
        'user_id', 'user_name', 'user_email', 'action', 'entity_type', 'entity_id',
        'before', 'after', 'ip', 'user_agent',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];
}
