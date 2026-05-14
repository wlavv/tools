<?php

namespace Modules\PermissionRoleManager\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\PermissionRoleManager\Models\PermissionPermission;
use Modules\PermissionRoleManager\Services\PermissionAuditService;
use Modules\PermissionRoleManager\Services\PermissionSyncService;

class PermissionPermissionController extends Controller
{
    public function index(Request $request)
    {
        $permissions = PermissionPermission::query()
            ->when($request->q, fn($q) => $q->where('key', 'like', '%' . $request->q . '%')->orWhere('label', 'like', '%' . $request->q . '%'))
            ->when($request->module, fn($q) => $q->where('module', $request->module))
            ->orderBy('module')->orderBy('key')
            ->paginate(30);

        $modules = PermissionPermission::select('module')->whereNotNull('module')->distinct()->orderBy('module')->pluck('module');

        return $this->view('permission-role-manager::permissions.index', compact('permissions', 'modules'));
    }

    public function create()
    {
        return $this->view('permission-role-manager::permissions.form', ['permission' => new PermissionPermission()]);
    }

    public function store(Request $request, PermissionAuditService $audit)
    {
        $permission = PermissionPermission::create($this->validated($request));
        $audit->log('permission.created', 'permission', $permission->id, null, $permission->toArray());
        return redirect()->route('permission_role_manager.permissions.index')->with('success', 'Permission criada com sucesso.');
    }

    public function edit(PermissionPermission $permission)
    {
        return $this->view('permission-role-manager::permissions.form', compact('permission'));
    }

    public function update(Request $request, PermissionPermission $permission, PermissionAuditService $audit)
    {
        $before = $permission->toArray();
        $permission->update($this->validated($request));
        $audit->log('permission.updated', 'permission', $permission->id, $before, $permission->fresh()->toArray());
        return redirect()->route('permission_role_manager.permissions.index')->with('success', 'Permission atualizada com sucesso.');
    }

    public function destroy(PermissionPermission $permission, PermissionAuditService $audit)
    {
        if ($permission->is_system) {
            return back()->with('error', 'Permissions de sistema não podem ser removidas. Podes desativar.');
        }
        $before = $permission->toArray();
        $permission->delete();
        $audit->log('permission.deleted', 'permission', $permission->id, $before, null);
        return back()->with('success', 'Permission removida.');
    }

    public function toggle(PermissionPermission $permission, PermissionAuditService $audit)
    {
        $before = $permission->toArray();
        $permission->update(['is_active' => !$permission->is_active]);
        $audit->log('permission.toggled', 'permission', $permission->id, $before, $permission->fresh()->toArray());
        return back()->with('success', 'Estado da permission atualizado.');
    }

    public function syncBase(PermissionSyncService $sync, PermissionAuditService $audit)
    {
        $result = $sync->syncFromModuleConfigs();
        $audit->log('permissions.synced_from_configs', 'permission', null, null, $result);
        return back()->with('success', 'Sync concluído: ' . $result['created'] . ' criadas, ' . $result['updated'] . ' atualizadas.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'key' => ['required', 'string', 'max:255'],
            'label' => ['required', 'string', 'max:255'],
            'module' => ['nullable', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:255'],
            'risk' => ['required', 'in:low,medium,high,critical'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
