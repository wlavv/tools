<?php

namespace Modules\PermissionRoleManager\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Modules\PermissionRoleManager\Models\PermissionPermission;
use Modules\PermissionRoleManager\Models\PermissionRole;
use Modules\PermissionRoleManager\Services\EffectivePermissionService;
use Modules\PermissionRoleManager\Services\PermissionAuditService;

class PermissionUserController extends Controller
{
    public function index(Request $request)
    {
        $userModel = config('permission-role-manager.user_model');
        abort_unless(class_exists($userModel), 500, 'User model não encontrado.');

        $users = $userModel::query()
            ->when($request->q, fn($q) => $q->where('name', 'like', '%' . $request->q . '%')->orWhere('email', 'like', '%' . $request->q . '%'))
            ->orderBy('name')
            ->paginate(25);

        $userIds = $users->pluck('id')->all();
        $roleCounts = DB::table('permission_user_role')->whereIn('user_id', $userIds)->selectRaw('user_id, count(*) as total')->groupBy('user_id')->pluck('total', 'user_id');

        return $this->view('permission-role-manager::users.index', compact('users', 'roleCounts'));
    }

    public function create()
    {
        $userModel = config('permission-role-manager.user_model');
        abort_unless(class_exists($userModel), 500, 'User model não encontrado.');

        $roles = $this->manualActiveRoles();

        return $this->view('permission-role-manager::users.create', compact('roles'));
    }

    public function store(Request $request, PermissionAuditService $audit)
    {
        $userModel = config('permission-role-manager.user_model');
        abort_unless(class_exists($userModel), 500, 'User model não encontrado.');

        $user = new $userModel();
        $usersTable = $user->getTable();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:' . $usersTable . ',email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['array'],
            'roles.*' => ['integer', 'exists:permission_roles,id'],
        ]);

        $roleIds = $this->manualActiveRoles()
            ->whereIn('id', $data['roles'] ?? [])
            ->pluck('id')
            ->all();

        $createdUser = DB::transaction(function () use ($userModel, $data, $roleIds) {
            $createdUser = $userModel::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            foreach ($roleIds as $roleId) {
                DB::table('permission_user_role')->insert([
                    'user_id' => $createdUser->id,
                    'permission_role_id' => $roleId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $createdUser;
        });

        $audit->log('user.created', 'user', $createdUser->id, null, [
            'id' => $createdUser->id,
            'name' => $createdUser->name,
            'email' => $createdUser->email,
            'roles' => $roleIds,
        ]);

        return redirect()
            ->route('permission_role_manager.users.edit', $createdUser->id)
            ->with('success', 'User criado com sucesso. Pode agora ajustar roles e permissions.');
    }

    public function edit(int $user)
    {
        $userModel = config('permission-role-manager.user_model');
        abort_unless(class_exists($userModel), 500, 'User model não encontrado.');

        $userRecord = $userModel::findOrFail($user);
        $roles = $this->manualActiveRoles();
        $selectedRoles = DB::table('permission_user_role')->where('user_id', $user)->pluck('permission_role_id')->toArray();

        return $this->view('permission-role-manager::users.edit', compact('userRecord', 'roles', 'selectedRoles'));
    }

    public function update(Request $request, int $user, PermissionAuditService $audit)
    {
        $userModel = config('permission-role-manager.user_model');
        abort_unless(class_exists($userModel), 500, 'User model nao encontrado.');

        $userRecord = $userModel::findOrFail($user);
        $usersTable = $userRecord->getTable();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique($usersTable, 'email')->ignore($userRecord->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $before = [
            'name' => $userRecord->name,
            'email' => $userRecord->email,
            'password_changed' => false,
        ];

        $userRecord->name = $data['name'];
        $userRecord->email = $data['email'];

        $passwordChanged = filled($data['password'] ?? null);

        if ($passwordChanged) {
            $userRecord->password = Hash::make($data['password']);
        }

        $userRecord->save();

        $audit->log('user.updated', 'user', $userRecord->id, $before, [
            'name' => $userRecord->name,
            'email' => $userRecord->email,
            'password_changed' => $passwordChanged,
        ]);

        return back()->with('success', $passwordChanged
            ? 'Dados e password do utilizador atualizados.'
            : 'Dados do utilizador atualizados.');
    }

    public function syncRoles(Request $request, int $user, PermissionAuditService $audit)
    {
        $roleIds = $this->manualActiveRoles()
            ->whereIn('id', $request->input('roles', []))
            ->pluck('id')
            ->all();

        $before = DB::table('permission_user_role')->where('user_id', $user)->pluck('permission_role_id')->toArray();
        DB::table('permission_user_role')->where('user_id', $user)->delete();
        foreach ($roleIds as $roleId) {
            DB::table('permission_user_role')->insert(['user_id' => $user, 'permission_role_id' => $roleId, 'created_at' => now(), 'updated_at' => now()]);
        }
        $after = DB::table('permission_user_role')->where('user_id', $user)->pluck('permission_role_id')->toArray();
        $audit->log('user.roles.synced', 'user', $user, ['roles' => $before], ['roles' => $after]);
        return back()->with('success', 'Roles do utilizador atualizadas.');
    }

    public function syncPermissions(Request $request, int $user, PermissionAuditService $audit)
    {
        $before = DB::table('permission_user_permission')->where('user_id', $user)->pluck('permission_permission_id')->toArray();
        DB::table('permission_user_permission')->where('user_id', $user)->delete();
        foreach ($request->input('permissions', []) as $permissionId) {
            DB::table('permission_user_permission')->insert(['user_id' => $user, 'permission_permission_id' => $permissionId, 'created_at' => now(), 'updated_at' => now()]);
        }
        $after = DB::table('permission_user_permission')->where('user_id', $user)->pluck('permission_permission_id')->toArray();
        $audit->log('user.permissions.synced', 'user', $user, ['permissions' => $before], ['permissions' => $after]);
        return back()->with('success', 'Permissions diretas do utilizador atualizadas.');
    }

    private function manualActiveRoles()
    {
        return PermissionRole::where('is_active', true)
            ->where(function ($query) {
                $query->where('is_system', false)
                    ->orWhere('slug', 'not like', 'route-access-%');
            })
            ->orderBy('name')
            ->get();
    }
}
