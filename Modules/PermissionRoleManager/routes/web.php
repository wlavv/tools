<?php

use Illuminate\Support\Facades\Route;
use Modules\PermissionRoleManager\Http\Controllers\PermissionAuditController;
use Modules\PermissionRoleManager\Http\Controllers\PermissionDashboardController;
use Modules\PermissionRoleManager\Http\Controllers\PermissionInspectorController;
use Modules\PermissionRoleManager\Http\Controllers\PermissionMatrixController;
use Modules\PermissionRoleManager\Http\Controllers\PermissionPermissionController;
use Modules\PermissionRoleManager\Http\Controllers\PermissionRoleController;
use Modules\PermissionRoleManager\Http\Controllers\PermissionRouteAccessController;
use Modules\PermissionRoleManager\Http\Controllers\PermissionSettingsController;
use Modules\PermissionRoleManager\Http\Controllers\PermissionUserController;

Route::middleware(['web', 'auth'])
    ->prefix(config('permission-role-manager.route_prefix', 'permission-role-manager'))
    ->name(config('permission-role-manager.route_name', 'permission_role_manager.'))
    ->group(function () {
        Route::get('/', PermissionDashboardController::class)->name('dashboard');

        Route::resource('roles', PermissionRoleController::class)->except(['show']);
        Route::post('roles/{role}/toggle', [PermissionRoleController::class, 'toggle'])->name('roles.toggle');
        Route::post('roles/{role}/permissions', [PermissionRoleController::class, 'syncPermissions'])->name('roles.permissions.sync');

        Route::resource('permissions', PermissionPermissionController::class)->except(['show']);
        Route::post('permissions/sync/base', [PermissionPermissionController::class, 'syncBase'])->name('permissions.sync');
        Route::post('permissions/{permission}/toggle', [PermissionPermissionController::class, 'toggle'])->name('permissions.toggle');

        Route::get('users', [PermissionUserController::class, 'index'])->name('users.index');
        Route::get('users/create', [PermissionUserController::class, 'create'])->name('users.create');
        Route::post('users', [PermissionUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}', [PermissionUserController::class, 'edit'])->name('users.edit');
        Route::post('users/{user}/roles', [PermissionUserController::class, 'syncRoles'])->name('users.roles.sync');
        Route::post('users/{user}/permissions', [PermissionUserController::class, 'syncPermissions'])->name('users.permissions.sync');

        Route::get('matrix', [PermissionMatrixController::class, 'index'])->name('matrix.index');
        Route::post('matrix/toggle', [PermissionMatrixController::class, 'toggle'])->name('matrix.toggle');
        Route::post('matrix/module/toggle', [PermissionMatrixController::class, 'toggleModule'])->name('matrix.module.toggle');

        Route::get('route-access', [PermissionRouteAccessController::class, 'index'])->name('route_access.index');
        Route::post('route-access/permissions', [PermissionRouteAccessController::class, 'syncPermissions'])->name('route_access.permissions.sync');
        Route::post('route-access/auto-profiles/archive', [PermissionRouteAccessController::class, 'archiveAutoProfiles'])->name('route_access.auto_profiles.archive');

        Route::get('inspector', [PermissionInspectorController::class, 'index'])->name('inspector.index');
        Route::get('audit', [PermissionAuditController::class, 'index'])->name('audit.index');
        Route::get('settings', [PermissionSettingsController::class, 'index'])->name('settings.index');
    });
