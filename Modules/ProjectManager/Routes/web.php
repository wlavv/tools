<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectManager\Http\Controllers\ProjectManagerController;

Route::middleware(['web', 'auth'])
    ->prefix('project-manager')
    ->name('project_manager.')
    ->group(function () {
        Route::get('/', [ProjectManagerController::class, 'index'])->name('index');
        Route::get('/create', [ProjectManagerController::class, 'create'])->name('create');
        Route::post('/', [ProjectManagerController::class, 'store'])->name('store');
        Route::get('/{project}', [ProjectManagerController::class, 'show'])->name('show');
        Route::get('/{project}/edit', [ProjectManagerController::class, 'edit'])->name('edit');
        Route::put('/{project}', [ProjectManagerController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectManagerController::class, 'destroy'])->name('destroy');

        Route::post('/{project}/tasks', [ProjectManagerController::class, 'storeTask'])->name('tasks.store');
        Route::put('/{project}/tasks/{task}', [ProjectManagerController::class, 'updateTask'])->name('tasks.update');
        Route::delete('/{project}/tasks/{task}', [ProjectManagerController::class, 'destroyTask'])->name('tasks.destroy');
        Route::post('/{project}/tasks/{task}/toggle', [ProjectManagerController::class, 'toggleTask'])->name('tasks.toggle');
    });
