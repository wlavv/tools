<?php

namespace Modules\PermissionRoleManager\Services;

use Illuminate\Support\Facades\DB;
use Modules\PermissionRoleManager\Models\PermissionPermission;
use Modules\PermissionRoleManager\Models\PermissionRole;

class EffectivePermissionService
{
    public function forUser(int $userId): array
    {
        $roles = PermissionRole::query()
            ->join('permission_user_role', 'permission_roles.id', '=', 'permission_user_role.permission_role_id')
            ->where('permission_user_role.user_id', $userId)
            ->where('permission_roles.is_active', true)
            ->whereNull('permission_roles.deleted_at')
            ->select('permission_roles.*')
            ->with('permissions')
            ->get();

        $effective = [];

        foreach ($roles as $role) {
            foreach ($role->permissions as $permission) {
                $effective[$permission->key] = $effective[$permission->key] ?? [
                    'permission' => $permission,
                    'sources' => [],
                ];
                $effective[$permission->key]['sources'][] = 'Role: ' . $role->name;
            }
        }

        ksort($effective);

        return [
            'roles' => $roles,
            'direct_permissions' => collect(),
            'effective' => $effective,
        ];
    }
}
