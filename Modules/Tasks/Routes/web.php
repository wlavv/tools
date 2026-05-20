<?php

use Illuminate\Support\Facades\Route;
use Modules\Tasks\Http\Controllers\TasksController;

$registerTasksRoutes = function (bool $named = false): void {
    $name = fn ($route, string $routeName) => $named ? $route->name($routeName) : $route;

    $name(Route::get('/', [TasksController::class, 'index']), 'index');
    $name(Route::get('/dashboard/{year?}/{month?}', [TasksController::class, 'dashboard']), 'dashboard');
    $name(Route::post('/update', [TasksController::class, 'updateDone']), 'updateDone');
    $name(Route::get('/calendar/{year}/{month}', [TasksController::class, 'calendar'])->whereNumber('year')->whereNumber('month'), 'calendar');

    $name(Route::get('/members', [TasksController::class, 'members']), 'members.index');
    $name(Route::post('/members', [TasksController::class, 'storeMember']), 'members.store');
    $name(Route::post('/members/{member}', [TasksController::class, 'updateMember']), 'members.update');
    $name(Route::post('/members/{member}/delete', [TasksController::class, 'deleteMember']), 'members.delete');

    $name(Route::get('/events', [TasksController::class, 'events']), 'events.index');
    $name(Route::post('/events', [TasksController::class, 'storeEvent']), 'events.store');
    $name(Route::post('/events/{event}', [TasksController::class, 'updateEvent']), 'events.update');
    $name(Route::post('/events/{event}/delete', [TasksController::class, 'deleteEvent']), 'events.delete');

    $name(Route::get('/manage', [TasksController::class, 'manageTasks']), 'manage.index');
    $name(Route::post('/manage', [TasksController::class, 'storeTask']), 'manage.store');
    $name(Route::post('/manage/{task}', [TasksController::class, 'updateTask']), 'manage.update');
    $name(Route::post('/manage/{task}/delete', [TasksController::class, 'deleteTask']), 'manage.delete');

    $name(Route::get('/rewards/{year?}/{month?}', [TasksController::class, 'rewards']), 'rewards.index');
    $name(Route::post('/rewards/default', [TasksController::class, 'storeRewardLevel']), 'rewards.default.store');
    $name(Route::post('/rewards/default/{reward}', [TasksController::class, 'updateRewardLevel']), 'rewards.default.update');
    $name(Route::post('/rewards/default/{reward}/delete', [TasksController::class, 'deleteRewardLevel']), 'rewards.default.delete');

    $name(Route::post('/rewards/override', [TasksController::class, 'storeRewardOverride']), 'rewards.override.store');
    $name(Route::post('/rewards/override/{override}', [TasksController::class, 'updateRewardOverride']), 'rewards.override.update');
    $name(Route::post('/rewards/override/{override}/delete', [TasksController::class, 'deleteRewardOverride']), 'rewards.override.delete');

    $name(Route::get('/tablet', [TasksController::class, 'tablet']), 'tablet');
    $name(Route::post('/tablet/task-toggle', [TasksController::class, 'tabletToggleTask']), 'tablet.task.toggle');
    $name(Route::post('/tablet/events', [TasksController::class, 'tabletStoreEvent']), 'tablet.event.store');
};

Route::prefix(config('tasks.route_prefix', 'family/tasks'))->name('tasks.')->middleware(config('tasks.middleware', ['web', 'auth']))->group(fn () => $registerTasksRoutes(true));
Route::prefix('tasks')->middleware(config('tasks.middleware', ['web', 'auth']))->group(fn () => $registerTasksRoutes(false));
Route::prefix('hr/tasks')->middleware(config('tasks.middleware', ['web', 'auth']))->group(fn () => $registerTasksRoutes(false));

Route::prefix('hub')->middleware(['web'])->group(function () {
    Route::get('/tablet',                       [TasksController::class, 'tabletPublic'])->name('tasks.tablet.public');
    Route::get('/assets/member/{slug}',         [TasksController::class, 'tabletMemberAsset'])->where('slug', '.*')->name('tasks.tablet.asset.member');
    Route::get('/assets/weather/{file}',        [TasksController::class, 'tabletWeatherAsset'])->where('file', '.*')->name('tasks.tablet.asset.weather');
});
