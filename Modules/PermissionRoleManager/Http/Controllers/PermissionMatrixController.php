<?php

namespace Modules\PermissionRoleManager\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Modules\PermissionRoleManager\Models\PermissionPermission;
use Modules\PermissionRoleManager\Models\PermissionRole;
use Modules\PermissionRoleManager\Services\PermissionAuditService;

class PermissionMatrixController extends Controller
{
    public function index(Request $request)
    {
        $roles = PermissionRole::where('is_active', true)
            ->where(function ($query) {
                $query->where('is_system', false)
                    ->orWhere('slug', 'not like', 'route-access-%');
            })
            ->orderBy('name')
            ->get();

        $permissions = PermissionPermission::query()
            ->where('is_active', true)
            ->when($request->module, fn($q) => $q->where('module', $request->module))
            ->orderBy('module')->orderBy('key')->get();
        $modules = PermissionPermission::select('module')->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');
        $assigned = DB::table('permission_role_permission')->get()->mapWithKeys(fn($row) => [$row->permission_role_id . ':' . $row->permission_permission_id => true]);
        $groupedPermissions = $permissions->groupBy(fn($permission) => $permission->module ?: 'Sem modulo');
        $moduleRoleStats = [];

        foreach ($groupedPermissions as $module => $modulePermissions) {
            foreach ($roles as $role) {
                $assignedCount = $modulePermissions
                    ->filter(fn($permission) => isset($assigned[$role->id . ':' . $permission->id]))
                    ->count();

                $moduleRoleStats[$module][$role->id] = [
                    'assigned' => $assignedCount,
                    'total' => $modulePermissions->count(),
                ];
            }
        }

        return $this->view('permission-role-manager::matrix.index', compact('roles', 'permissions', 'modules', 'assigned', 'groupedPermissions', 'moduleRoleStats'));
    }

    public function toggle(Request $request, PermissionAuditService $audit)
    {
        $data = $request->validate([
            'role_id' => ['required', 'integer', 'exists:permission_roles,id'],
            'permission_id' => ['required', 'integer', 'exists:permission_permissions,id'],
        ]);

        $role = PermissionRole::findOrFail($data['role_id']);
        if ($role->is_system && str_starts_with($role->slug, 'route-access-')) {
            return back()->with('error', 'Perfis automaticos nao podem ser alterados na matrix.');
        }

        $exists = DB::table('permission_role_permission')
            ->where('permission_role_id', $data['role_id'])
            ->where('permission_permission_id', $data['permission_id'])
            ->exists();

        if ($exists) {
            DB::table('permission_role_permission')
                ->where('permission_role_id', $data['role_id'])
                ->where('permission_permission_id', $data['permission_id'])
                ->delete();
            $action = 'matrix.permission.detached';
            $assigned = false;
        } else {
            DB::table('permission_role_permission')->insert([
                'permission_role_id' => $data['role_id'],
                'permission_permission_id' => $data['permission_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $action = 'matrix.permission.attached';
            $assigned = true;
        }

        $audit->log($action, 'role_permission', null, null, $data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'assigned' => $assigned,
                'role_id' => $data['role_id'],
                'permission_id' => $data['permission_id'],
            ]);
        }

        return back()->with('success', 'Matrix atualizada.');
    }

    public function toggleModule(Request $request, PermissionAuditService $audit)
    {
        $data = $request->validate([
            'role_id' => ['required', 'integer', 'exists:permission_roles,id'],
            'module' => ['required', 'string'],
        ]);

        $role = PermissionRole::findOrFail($data['role_id']);
        if ($role->is_system && str_starts_with($role->slug, 'route-access-')) {
            return back()->with('error', 'Perfis automaticos nao podem ser alterados na matrix.');
        }

        $permissionIds = PermissionPermission::query()
            ->where('is_active', true)
            ->when(
                $data['module'] === 'Sem modulo',
                fn($query) => $query->whereNull('module'),
                fn($query) => $query->where('module', $data['module'])
            )
            ->pluck('id')
            ->all();

        if ($permissionIds === []) {
            return back()->with('error', 'Modulo sem permissions ativas.');
        }

        $assignedCount = DB::table('permission_role_permission')
            ->where('permission_role_id', $role->id)
            ->whereIn('permission_permission_id', $permissionIds)
            ->count();

        if ($assignedCount === count($permissionIds)) {
            DB::table('permission_role_permission')
                ->where('permission_role_id', $role->id)
                ->whereIn('permission_permission_id', $permissionIds)
                ->delete();
            $action = 'matrix.module.detached';
            $message = 'Permissions do modulo removidas do perfil.';
            $assigned = false;
            $newAssignedCount = 0;
        } else {
            $existing = DB::table('permission_role_permission')
                ->where('permission_role_id', $role->id)
                ->whereIn('permission_permission_id', $permissionIds)
                ->pluck('permission_permission_id')
                ->all();

            $now = now();
            $rows = collect($permissionIds)
                ->diff($existing)
                ->map(fn($permissionId) => [
                    'permission_role_id' => $role->id,
                    'permission_permission_id' => $permissionId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->values()
                ->all();

            if ($rows !== []) {
                DB::table('permission_role_permission')->insert($rows);
            }

            $action = 'matrix.module.attached';
            $message = 'Permissions do modulo adicionadas ao perfil.';
            $assigned = true;
            $newAssignedCount = count($permissionIds);
        }

        $audit->log($action, 'role_permission', $role->id, null, [
            'role_id' => $role->id,
            'module' => $data['module'],
            'permissions' => $permissionIds,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'assigned' => $assigned,
                'role_id' => $role->id,
                'module' => $data['module'],
                'permission_ids' => $permissionIds,
                'assigned_count' => $newAssignedCount,
                'total' => count($permissionIds),
            ]);
        }

        return back()->with('success', $message);
    }
}
