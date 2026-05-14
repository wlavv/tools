<?php

namespace Modules\PermissionRoleManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\PermissionRoleManager\Models\PermissionAuditLog;
use Modules\PermissionRoleManager\Models\PermissionPermission;
use Modules\PermissionRoleManager\Models\PermissionRole;

class PermissionDashboardController extends Controller
{
    public function __invoke()
    {
        $userModel = config('permission-role-manager.user_model');

        return $this->view('permission-role-manager::dashboard.index', [
            'rolesCount' => PermissionRole::count(),
            'permissionsCount' => PermissionPermission::count(),
            'criticalPermissionsCount' => PermissionPermission::where('risk', 'critical')->count(),
            'usersCount' => class_exists($userModel) ? $userModel::count() : 0,
            'recentLogs' => PermissionAuditLog::latest()->limit(8)->get(),
        ]);
    }
}
