<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (
            ! Schema::hasTable('permission_roles')
            || ! Schema::hasTable('permission_permissions')
            || ! Schema::hasTable('permission_role_permission')
        ) {
            return;
        }

        $roleId = DB::table('permission_roles')
            ->where('slug', 'humanresources')
            ->whereNull('deleted_at')
            ->value('id');

        if (! $roleId) {
            return;
        }

        $now = now();
        $permissions = [
            'route.hr.index' => ['label' => 'Route hr.index', 'module' => 'BackOfficeAreas'],
            'route.tasks.index' => ['label' => 'Route tasks.index', 'module' => 'Tasks'],
            'route.tasks.dashboard' => ['label' => 'Route tasks.dashboard', 'module' => 'Tasks'],
            'route.tasks.calendar' => ['label' => 'Route tasks.calendar', 'module' => 'Tasks'],
            'route.tasks.updateDone' => ['label' => 'Route tasks.updateDone', 'module' => 'Tasks', 'risk' => 'high'],
            'route.tasks.tablet' => ['label' => 'Route tasks.tablet', 'module' => 'Tasks'],
        ];

        foreach ($permissions as $key => $permission) {
            $permissionId = DB::table('permission_permissions')
                ->where('key', $key)
                ->value('id');

            $payload = [
                'key' => $key,
                'label' => $permission['label'],
                'module' => $permission['module'],
                'group' => 'Routes',
                'risk' => $permission['risk'] ?? 'medium',
                'description' => $permission['description'] ?? null,
                'is_system' => true,
                'is_active' => true,
                'deleted_at' => null,
                'updated_at' => $now,
            ];

            if ($permissionId) {
                DB::table('permission_permissions')->where('id', $permissionId)->update($payload);
            } else {
                $permissionId = DB::table('permission_permissions')->insertGetId($payload + [
                    'created_at' => $now,
                ]);
            }

            $exists = DB::table('permission_role_permission')
                ->where('permission_role_id', $roleId)
                ->where('permission_permission_id', $permissionId)
                ->exists();

            if (! $exists) {
                DB::table('permission_role_permission')->insert([
                    'permission_role_id' => $roleId,
                    'permission_permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        if (
            ! Schema::hasTable('permission_roles')
            || ! Schema::hasTable('permission_permissions')
            || ! Schema::hasTable('permission_role_permission')
        ) {
            return;
        }

        $roleId = DB::table('permission_roles')
            ->where('slug', 'humanresources')
            ->value('id');

        if (! $roleId) {
            return;
        }

        $permissionIds = DB::table('permission_permissions')
            ->whereIn('key', [
                'route.hr.index',
                'route.tasks.index',
                'route.tasks.dashboard',
                'route.tasks.calendar',
                'route.tasks.updateDone',
                'route.tasks.tablet',
            ])
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('permission_role_permission')
                ->where('permission_role_id', $roleId)
                ->whereIn('permission_permission_id', $permissionIds)
                ->delete();
        }
    }
};
