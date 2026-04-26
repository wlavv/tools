<?php
use Illuminate\Support\Facades\Route;
use Modules\ProjectManager\Http\Controllers\ProjectManagerController;

Route::middleware(['web','auth'])->prefix('administration/project-manager')->name('project_manager.')->group(function(){
    Route::get('/', [ProjectManagerController::class,'index'])->name('index');
    Route::get('/create', [ProjectManagerController::class,'create'])->name('create');
    Route::post('/', [ProjectManagerController::class,'store'])->name('store');

    Route::get('/{project}/tasks/create', [ProjectManagerController::class,'createTask'])->name('tasks.create');
    Route::post('/{project}/tasks', [ProjectManagerController::class,'storeTask'])->name('tasks.store');
    Route::get('/tasks/{task}/edit', [ProjectManagerController::class,'editTask'])->name('tasks.edit');
    Route::put('/tasks/{task}', [ProjectManagerController::class,'updateTask'])->name('tasks.update');
    Route::delete('/tasks/{task}', [ProjectManagerController::class,'destroyTask'])->name('tasks.destroy');
    Route::post('/tasks/{task}/complete', [ProjectManagerController::class,'completeTask'])->name('tasks.complete');
    Route::post('/tasks/{task}/reopen', [ProjectManagerController::class,'reopenTask'])->name('tasks.reopen');

    Route::get('/{project}', [ProjectManagerController::class,'show'])->name('show');
    Route::get('/{project}/edit', [ProjectManagerController::class,'edit'])->name('edit');
    Route::put('/{project}', [ProjectManagerController::class,'update'])->name('update');
    Route::delete('/{project}', [ProjectManagerController::class,'destroy'])->name('destroy');
});
