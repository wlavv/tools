<?php

use Illuminate\Support\Facades\Route;
use Modules\ModuleComplianceCenter\Http\Controllers\ComplianceModuleController;
use Modules\ModuleComplianceCenter\Http\Controllers\ComplianceReportController;
use Modules\ModuleComplianceCenter\Http\Controllers\ComplianceRunController;
use Modules\ModuleComplianceCenter\Http\Controllers\ComplianceValidatorController;
use Modules\ModuleComplianceCenter\Http\Controllers\ModuleComplianceCenterController;

Route::middleware(['web', 'auth'])
    ->prefix(config('module-compliance-center.routes.web_prefix', 'admin/module-compliance-center'))
    ->as('module_compliance_center.')
    ->group(function () {
        Route::get('/', [ModuleComplianceCenterController::class, 'index'])->name('dashboard');

        Route::post('modules/discover', [ComplianceModuleController::class, 'discover'])->name('modules.discover');
        Route::resource('modules', ComplianceModuleController::class)->only(['index', 'show']);

        Route::post('validators/sync', [ComplianceValidatorController::class, 'sync'])->name('validators.sync');
        Route::post('validators/{validator}/enable', [ComplianceValidatorController::class, 'enable'])->name('validators.enable');
        Route::post('validators/{validator}/disable', [ComplianceValidatorController::class, 'disable'])->name('validators.disable');
        Route::resource('validators', ComplianceValidatorController::class)->only(['index']);

        Route::post('runs/{run}/approve', [ComplianceRunController::class, 'approve'])->name('runs.approve');
        Route::post('runs/{run}/reject', [ComplianceRunController::class, 'reject'])->name('runs.reject');
        Route::post('runs/{run}/request-changes', [ComplianceRunController::class, 'requestChanges'])->name('runs.request_changes');
        Route::post('runs/{run}/send-to-ai', [ComplianceRunController::class, 'sendToAI'])->name('runs.send_to_ai');
        Route::post('runs/{run}/create-project-tasks', [ComplianceRunController::class, 'createProjectTasks'])->name('runs.create_project_tasks');
        Route::post('runs/rerun-all', [ComplianceRunController::class, 'rerunAll'])->name('runs.rerun_all');
        Route::resource('runs', ComplianceRunController::class)->only(['index', 'create', 'store', 'show']);

        Route::get('runs/{run}/report', [ComplianceReportController::class, 'show'])->name('reports.show');
        Route::get('reports/{report}/export', [ComplianceReportController::class, 'export'])->name('reports.export');
    });
