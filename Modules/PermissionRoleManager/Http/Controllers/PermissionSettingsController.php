<?php

namespace Modules\PermissionRoleManager\Http\Controllers;

use App\Http\Controllers\Controller;

class PermissionSettingsController extends Controller
{
    public function index()
    {
        return $this->view('permission-role-manager::settings.index', [
            'config' => config('permission-role-manager'),
        ]);
    }
}
