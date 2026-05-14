<?php

use Illuminate\Support\Facades\Route;
use Modules\AuditLogCentral\Http\Controllers\AuditLogController;

Route::middleware(['web', 'auth'])
    ->prefix(config('audit-log-central.route_prefix', 'audit-log-central'))
    ->name(config('audit-log-central.route_name', 'audit_log_central.'))
    ->group(function () {
        Route::get('/', [AuditLogController::class, 'dashboard'])->name('dashboard');
        Route::get('/logs', [AuditLogController::class, 'index'])->name('index');
        Route::get('/logs/{auditLog}', [AuditLogController::class, 'show'])->name('show');
        Route::get('/entity/{entityType}/{entityId}', [AuditLogController::class, 'entityTimeline'])->name('entity.timeline');
    });
