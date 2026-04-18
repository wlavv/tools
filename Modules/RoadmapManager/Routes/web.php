<?php

use Illuminate\Support\Facades\Route;
use Modules\RoadmapManager\Http\Controllers\DashboardController;
use Modules\RoadmapManager\Http\Controllers\RoadmapGroupController;
use Modules\RoadmapManager\Http\Controllers\RoadmapProjectController;
use Modules\RoadmapManager\Http\Controllers\MilestoneController;
use Modules\RoadmapManager\Http\Controllers\TaskController;

Route::middleware(['web', 'auth'])->prefix(config('roadmap-manager.route_prefix', 'roadmap'))->name('roadmap.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('groups', RoadmapGroupController::class)->except(['destroy']);
    Route::resource('projects', RoadmapProjectController::class)->except(['destroy']);
    Route::resource('milestones', MilestoneController::class)->except(['destroy']);

    Route::get('tasks/tree', [TaskController::class, 'tree'])->name('tasks.tree');
    Route::get('tasks/gantt', [TaskController::class, 'gantt'])->name('tasks.gantt');
    Route::get('tasks/kanban', [TaskController::class, 'kanban'])->name('tasks.kanban');

    Route::post('tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store');
    Route::post('tasks/{task}/time-logs', [TaskController::class, 'storeTimeLog'])->name('tasks.time_logs.store');
    Route::post('tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store');

    Route::resource('tasks', TaskController::class)->except(['destroy']);
});
