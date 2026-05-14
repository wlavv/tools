<?php

namespace Modules\PermissionRoleManager\Services;

use Illuminate\Support\Facades\Auth;
use Modules\PermissionRoleManager\Models\PermissionAuditLog;

class PermissionAuditService
{
    public function log(string $action, ?string $entityType = null, ?int $entityId = null, ?array $before = null, ?array $after = null): void
    {
        if (!config('permission-role-manager.audit_enabled', true)) {
            return;
        }

        $user = Auth::user();

        PermissionAuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before' => $before,
            'after' => $after,
            'ip' => request()?->ip(),
            'user_agent' => substr((string) request()?->userAgent(), 0, 1000),
        ]);
    }
}
