<?php

namespace Modules\PermissionRoleManager\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\PermissionRoleManager\Models\PermissionAuditLog;

class PermissionAuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = PermissionAuditLog::query()
            ->when($request->q, fn($q) => $q->where('action', 'like', '%' . $request->q . '%')->orWhere('user_email', 'like', '%' . $request->q . '%'))
            ->latest()
            ->paginate(30);

        return $this->view('permission-role-manager::audit.index', compact('logs'));
    }
}
