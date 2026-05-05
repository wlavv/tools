<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Http\Controllers\TasksController;

Route::prefix('hr/tasks')->name('tasks.')->middleware(['auth'])->group(function () {
    Route::get('/',                             [TasksController::class, 'index'])->name('index');
    Route::get('/dashboard/{year?}/{month?}',   [TasksController::class, 'dashboard'])->name('dashboard');
    Route::post('/update',                      [TasksController::class, 'updateDone'])->name('updateDone');
    Route::get('/calendar/{year}/{month}',      [TasksController::class, 'calendar'])->whereNumber('year')->whereNumber('month')->name('calendar');

    Route::get('/members',                      [TasksController::class, 'members'])->name('members.index');
    Route::post('/members',                     [TasksController::class, 'storeMember'])->name('members.store');
    Route::post('/members/{member}',            [TasksController::class, 'updateMember'])->name('members.update');
    Route::post('/members/{member}/delete',     [TasksController::class, 'deleteMember'])->name('members.delete');

    Route::get('/manage',                       [TasksController::class, 'manageTasks'])->name('manage.index');
    Route::post('/manage',                      [TasksController::class, 'storeTask'])->name('manage.store');
    Route::post('/manage/{task}',               [TasksController::class, 'updateTask'])->name('manage.update');
    Route::post('/manage/{task}/delete',        [TasksController::class, 'deleteTask'])->name('manage.delete');

    Route::get('/rewards/{year?}/{month?}',     [TasksController::class, 'rewards'])->name('rewards.index');
    Route::post('/rewards/default',             [TasksController::class, 'storeRewardLevel'])->name('rewards.default.store');
    Route::post('/rewards/default/{reward}',    [TasksController::class, 'updateRewardLevel'])->name('rewards.default.update');
    Route::post('/rewards/default/{reward}/delete', [TasksController::class, 'deleteRewardLevel'])->name('rewards.default.delete');

    Route::post('/rewards/override',            [TasksController::class, 'storeRewardOverride'])->name('rewards.override.store');
    Route::post('/rewards/override/{override}', [TasksController::class, 'updateRewardOverride'])->name('rewards.override.update');
    Route::post('/rewards/override/{override}/delete', [TasksController::class, 'deleteRewardOverride'])->name('rewards.override.delete');

    Route::get('/tablet',                       [TasksController::class, 'tablet'])->name('tablet');
    Route::post('/tablet/task-toggle',          [TasksController::class, 'tabletToggleTask'])->name('tablet.task.toggle');
    Route::post('/tablet/events',               [TasksController::class, 'tabletStoreEvent'])->name('tablet.event.store');
});

Route::prefix('hub')->group(function () {
    Route::get('/tablet',                       [TasksController::class, 'tabletPublic'])->name('tasks.tablet.public');
    Route::get('/assets/member/{slug}',         [TasksController::class, 'tabletMemberAsset'])->where('slug', '.*')->name('tasks.tablet.asset.member');
    Route::get('/assets/weather/{file}',        [TasksController::class, 'tabletWeatherAsset'])->where('file', '.*')->name('tasks.tablet.asset.weather');
});