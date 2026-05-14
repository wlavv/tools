<?php

namespace Modules\PermissionRoleManager\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\PermissionRoleManager\Services\EffectivePermissionService;

class PermissionInspectorController extends Controller
{
    public function index(Request $request, EffectivePermissionService $effectivePermissionService)
    {
        $userModel = config('permission-role-manager.user_model');
        abort_unless(class_exists($userModel), 500, 'User model não encontrado.');

        $users = $userModel::query()->orderBy('name')->limit(200)->get();
        $selectedUser = $request->user_id ? $userModel::find($request->user_id) : null;
        $effective = $selectedUser ? $effectivePermissionService->forUser($selectedUser->id) : null;

        return $this->view('permission-role-manager::inspector.index', compact('users', 'selectedUser', 'effective'));
    }
}
