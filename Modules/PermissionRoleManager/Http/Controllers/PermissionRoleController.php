<?php

namespace Modules\PermissionRoleManager\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\PermissionRoleManager\Models\PermissionPermission;
use Modules\PermissionRoleManager\Models\PermissionRole;
use Modules\PermissionRoleManager\Services\PermissionAuditService;

class PermissionRoleController extends Controller
{
    public function index(Request $request)
    {
        $includeAutoProfiles = $request->boolean('include_auto');
        $autoProfilesCount = PermissionRole::where('is_system', true)
            ->where('slug', 'like', 'route-access-%')
            ->count();

        $roles = PermissionRole::withCount('permissions')
            ->when(!$includeAutoProfiles, fn($q) => $q->where(function ($query) {
                $query->where('is_system', false)
                    ->orWhere('slug', 'not like', 'route-access-%');
            }))
            ->when($request->q, fn($q) => $q->where(function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->q . '%')
                    ->orWhere('slug', 'like', '%' . $request->q . '%');
            }))
            ->orderBy('name')
            ->paginate(20);

        return $this->view('permission-role-manager::roles.index', compact('roles', 'autoProfilesCount', 'includeAutoProfiles'));
    }

    public function create()
    {
        $permissions = PermissionPermission::where('is_active', true)->orderBy('module')->orderBy('key')->get();
        return $this->view('permission-role-manager::roles.form', ['role' => new PermissionRole(), 'permissions' => $permissions, 'selectedPermissions' => []]);
    }

    public function store(Request $request, PermissionAuditService $audit)
    {
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $role = PermissionRole::create($data);
        $role->permissions()->sync($request->input('permissions', []));
        $audit->log('role.created', 'role', $role->id, null, $role->fresh('permissions')->toArray());
        return redirect()->route('permission_role_manager.roles.index')->with('success', 'Role criada com sucesso.');
    }

    public function edit(PermissionRole $role)
    {
        $permissions = PermissionPermission::where('is_active', true)->orderBy('module')->orderBy('key')->get();
        return $this->view('permission-role-manager::roles.form', [
            'role' => $role,
            'permissions' => $permissions,
            'selectedPermissions' => $role->permissions()->pluck('permission_permissions.id')->toArray(),
        ]);
    }

    public function update(Request $request, PermissionRole $role, PermissionAuditService $audit)
    {
        $before = $role->fresh('permissions')->toArray();
        $data = $this->validated($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $role->update($data);
        $role->permissions()->sync($request->input('permissions', []));
        $audit->log('role.updated', 'role', $role->id, $before, $role->fresh('permissions')->toArray());
        return redirect()->route('permission_role_manager.roles.index')->with('success', 'Role atualizada com sucesso.');
    }

    public function destroy(PermissionRole $role, PermissionAuditService $audit)
    {
        if ($role->is_system) {
            return back()->with('error', 'Roles de sistema não podem ser removidas.');
        }
        $before = $role->toArray();
        $role->delete();
        $audit->log('role.deleted', 'role', $role->id, $before, null);
        return back()->with('success', 'Role removida.');
    }

    public function toggle(PermissionRole $role, PermissionAuditService $audit)
    {
        if ($role->is_active && auth()->id() && $this->isLastActiveRoleForCurrentUser($role)) {
            return back()->with('error', 'Nao podes desativar o ultimo perfil ativo do teu user.');
        }

        $before = $role->toArray();
        $role->update(['is_active' => !$role->is_active]);
        $audit->log('role.toggled', 'role', $role->id, $before, $role->fresh()->toArray());
        return back()->with('success', 'Estado da role atualizado.');
    }

    public function syncPermissions(Request $request, PermissionRole $role, PermissionAuditService $audit)
    {
        $before = $role->fresh('permissions')->toArray();
        $role->permissions()->sync($request->input('permissions', []));
        $audit->log('role.permissions.synced', 'role', $role->id, $before, $role->fresh('permissions')->toArray());
        return back()->with('success', 'Permissões sincronizadas.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'guard_name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }

    private function isLastActiveRoleForCurrentUser(PermissionRole $role): bool
    {
        $userId = auth()->id();

        $hasRole = DB::table('permission_user_role')
            ->where('user_id', $userId)
            ->where('permission_role_id', $role->id)
            ->exists();

        if (!$hasRole) {
            return false;
        }

        $activeRolesCount = PermissionRole::query()
            ->join('permission_user_role', 'permission_roles.id', '=', 'permission_user_role.permission_role_id')
            ->where('permission_user_role.user_id', $userId)
            ->where('permission_roles.is_active', true)
            ->whereNull('permission_roles.deleted_at')
            ->count();

        return $activeRolesCount <= 1;
    }
}
