<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;

use Modules\Tasks\Http\Controllers\TasksController;

Route::get('/', function () { return view('auth.login'); });

Route::prefix('tablet/tasks')->name('tasks.tablet.public.')->group(function () {
    Route::get('/', [TasksController::class, 'tabletPublic'])->name('index');
    Route::post('/task-toggle', [TasksController::class, 'tabletPublicToggleTask'])->name('task.toggle');
    Route::post('/events', [TasksController::class, 'tabletPublicStoreEvent'])->name('event.store');
});

Auth::routes();

Route::get('/clear-cache', function () {
    Artisan::call('optimize:clear');
    return 'Cache cleared';
});