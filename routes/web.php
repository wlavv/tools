<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Admin\InfrastructureAiBackupController;
use App\Http\Controllers\Admin\InfrastructureDocumentationController;
use App\Http\Controllers\Admin\LsgAiGatewayController;

use Modules\Tasks\Http\Controllers\TasksController;

Route::get('/', function () { return view('auth.login'); });

Route::prefix('tablet/tasks')->name('tasks.tablet.public.')->group(function () {
    Route::get('/', [TasksController::class, 'tabletPublic'])->name('index');
    Route::post('/task-toggle', [TasksController::class, 'tabletPublicToggleTask'])->name('task.toggle');
    Route::post('/events', [TasksController::class, 'tabletPublicStoreEvent'])->name('event.store');
});

if (App::environment(['local', 'staging', 'testing'])) {
    // Internal smoke test only. Remove or protect further before enabling in production.
    Route::get('/admin/ai-test', [LsgAiGatewayController::class, 'smoke'])->middleware('auth')->name('admin.ai-test');
}

Route::middleware('auth')->prefix('admin/lsg-ai')->name('admin.lsg-ai.')->group(function () {
    Route::get('/', [LsgAiGatewayController::class, 'index'])->name('index');
    Route::post('/test', [LsgAiGatewayController::class, 'test'])->name('test');
});

Route::middleware('auth')
    ->prefix('admin/infrastructure/ai-backups')
    ->name('admin.infrastructure.ai-backups.')
    ->group(function () {
        Route::get('/', [InfrastructureAiBackupController::class, 'index'])->name('index');
        Route::post('/create', [InfrastructureAiBackupController::class, 'create'])->name('create');
        Route::get('/logs', [InfrastructureAiBackupController::class, 'logs'])->name('logs');
        Route::get('/{filename}', [InfrastructureAiBackupController::class, 'show'])->name('show');
        Route::get('/{filename}/download', [InfrastructureAiBackupController::class, 'download'])->name('download');
        Route::post('/{filename}/checksum', [InfrastructureAiBackupController::class, 'checksum'])->name('checksum');
        Route::get('/{filename}/manifest', [InfrastructureAiBackupController::class, 'manifest'])->name('manifest');
        Route::delete('/{filename}', [InfrastructureAiBackupController::class, 'destroy'])->name('destroy');
    });

Route::middleware('auth')
    ->prefix('admin/infrastructure/documentation')
    ->name('admin.infrastructure.documentation.')
    ->group(function () {
        Route::get('/', [InfrastructureDocumentationController::class, 'index'])->name('index');
        Route::get('/{slug}', [InfrastructureDocumentationController::class, 'show'])->name('show');
    });

Auth::routes();
