<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SuperAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        if (
            ! Schema::hasTable('permission_roles')
            || ! Schema::hasTable('permission_permissions')
            || ! Schema::hasTable('permission_role_permission')
        ) {
            $this->command?->warn('Permission Role Manager tables not found. Skipping Super Admin role.');
            return;
        }

        $now = now();

        $roleId = DB::table('permission_roles')->where('slug', 'super-admin')->value('id');

        if ($roleId) {
            DB::table('permission_roles')->where('id', $roleId)->update([
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'guard_name' => 'web',
                'description' => 'Perfil com todas as permissoes ativas.',
                'is_active' => true,
                'deleted_at' => null,
                'updated_at' => $now,
            ]);
        } else {
            $roleId = DB::table('permission_roles')->insertGetId([
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'guard_name' => 'web',
                'description' => 'Perfil com todas as permissoes ativas.',
                'color' => '#111827',
                'is_system' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissionIds = DB::table('permission_permissions')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->pluck('id');

        $existingPermissionIds = DB::table('permission_role_permission')
            ->where('permission_role_id', $roleId)
            ->pluck('permission_permission_id');

        $missingPermissionIds = $permissionIds->diff($existingPermissionIds)->values();

        if ($missingPermissionIds->isNotEmpty()) {
            DB::table('permission_role_permission')->insert(
                $missingPermissionIds->map(fn ($permissionId) => [
                    'permission_role_id' => $roleId,
                    'permission_permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        }

        $this->command?->info(sprintf(
            'Super Admin role ready: %d / %d active permissions.',
            $permissionIds->count(),
            $permissionIds->count()
        ));
    }
}
