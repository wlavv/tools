<?php

namespace Modules\PermissionRoleManager\Services;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RoutePermissionAccessService
{
    private array $permissionExists = [];
    private array $userPermissions = [];

    public function canAccessRouteName(?int $userId, string $routeName): bool
    {
        return $this->canAccessPermissionKey($userId, $this->permissionKeyForRouteName($routeName));
    }

    public function canAccessRoute(?int $userId, ?Route $route): bool
    {
        if (!$route) {
            return true;
        }

        return $this->canAccessPermissionKey($userId, $this->permissionKeyForRoute($route));
    }

    public function permissionKeyForRoute(Route $route): string
    {
        if ($route->getName()) {
            return $this->permissionKeyForRouteName($route->getName());
        }

        $methods = array_values(array_diff($route->methods(), ['HEAD']));

        return 'route.' . Str::slug(implode('_', $methods) . '_' . $route->uri(), '.');
    }

    public function permissionKeyForRouteName(string $routeName): string
    {
        return 'route.' . $routeName;
    }

    private function canAccessPermissionKey(?int $userId, string $permissionKey): bool
    {
        if ($userId === 1) {
            return true;
        }

        if (!$this->routePermissionExists($permissionKey)) {
            return true;
        }

        if (!$userId) {
            return true;
        }

        if (in_array($userId, config('permission-role-manager.route_access_super_user_ids', []), true)) {
            return true;
        }

        return in_array($permissionKey, $this->permissionsForUser($userId), true);
    }

    private function routePermissionExists(string $permissionKey): bool
    {
        if (array_key_exists($permissionKey, $this->permissionExists)) {
            return $this->permissionExists[$permissionKey];
        }

        try {
            if (!Schema::hasTable('permission_permissions')) {
                return $this->permissionExists[$permissionKey] = false;
            }

            return $this->permissionExists[$permissionKey] = DB::table('permission_permissions')
                ->where('key', $permissionKey)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->exists();
        } catch (\Throwable) {
            return $this->permissionExists[$permissionKey] = false;
        }
    }

    private function permissionsForUser(int $userId): array
    {
        if (array_key_exists($userId, $this->userPermissions)) {
            return $this->userPermissions[$userId];
        }

        $rolePermissions = DB::table('permission_permissions')
            ->join('permission_role_permission', 'permission_permissions.id', '=', 'permission_role_permission.permission_permission_id')
            ->join('permission_roles', 'permission_role_permission.permission_role_id', '=', 'permission_roles.id')
            ->join('permission_user_role', 'permission_roles.id', '=', 'permission_user_role.permission_role_id')
            ->where('permission_user_role.user_id', $userId)
            ->where('permission_roles.is_active', true)
            ->whereNull('permission_roles.deleted_at')
            ->where('permission_permissions.is_active', true)
            ->whereNull('permission_permissions.deleted_at')
            ->pluck('permission_permissions.key');

        return $this->userPermissions[$userId] = $rolePermissions
            ->unique()
            ->values()
            ->all();
    }
}
