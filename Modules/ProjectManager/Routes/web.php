<?php

use Illuminate\Support\Facades\Route;
use Modules\ProjectManager\Http\Controllers\DashboardController;
use Modules\ProjectManager\Http\Controllers\ProjectController;
use Modules\ProjectManager\Http\Controllers\SectionController;
use Modules\ProjectManager\Services\ProjectManagerSectionRegistry;

Route::middleware(config('project-manager.middleware', ['web', 'auth']))
    ->prefix(config('project-manager.route_prefix', 'project-manager'))
    ->name(config('project-manager.route_name', 'project_manager.'))
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/operations', [DashboardController::class, 'operations'])->name('operations');
        Route::get('/productivity', [DashboardController::class, 'productivity'])->name('productivity');
        Route::post('/tasks/{task}/panel', [DashboardController::class, 'moveGlobalTaskPanel'])->whereNumber('task')->name('tasks.panel');
        Route::post('/tasks/{task}/priority-matrix', [DashboardController::class, 'updateGlobalTaskMatrix'])->whereNumber('task')->name('tasks.priority_matrix');
        Route::post('/tasks/{task}/block', [DashboardController::class, 'blockGlobalTask'])->whereNumber('task')->name('tasks.block');
        Route::post('/quick-task', [DashboardController::class, 'storeQuickTask'])->name('quick_tasks.store');

        Route::resource('projects', ProjectController::class);

        Route::prefix('projects/{project}')
            ->name('projects.')
            ->group(function () {
                Route::get('/overview', [ProjectController::class, 'show'])->name('overview');
                Route::get('/tasks', [ProjectController::class, 'tasks'])->name('tasks.index');
                Route::get('/roadmap', [ProjectController::class, 'roadmap'])->name('roadmap.index');
                Route::get('/roadmap-items', [ProjectController::class, 'roadmap'])->name('roadmap_items.index');
                Route::get('/productivity', [ProjectController::class, 'productivity'])->name('productivity');
                Route::get('/details', [ProjectController::class, 'details'])->name('details');

                Route::get('/ajax/milestones/{milestone}/tasks', [ProjectController::class, 'milestoneTasks'])->name('ajax.milestone_tasks');
                Route::post('/tasks/{task}/block', [ProjectController::class, 'blockTask'])->name('tasks.block');
                Route::post('/tasks/{task}/panel', [ProjectController::class, 'moveTaskPanel'])->name('tasks.panel');
                Route::post('/tasks/{task}/priority-matrix', [ProjectController::class, 'updateTaskMatrix'])->name('tasks.priority_matrix');
                Route::post('/milestones/{milestone}/complete', [ProjectController::class, 'completeMilestone'])->whereNumber('milestone')->name('milestones.complete');
                Route::post('/status', [ProjectController::class, 'updateStatus'])->name('status.update');
                Route::post('/assets/upload', [ProjectController::class, 'uploadAsset'])->name('assets.upload');

                foreach (ProjectManagerSectionRegistry::all() as $section => $meta) {
                    if (in_array($section, ['tasks', 'roadmap-items'], true)) {
                        continue;
                    }

                    $uri = $meta['uri'] ?? $section;
                    $routeKey = ProjectManagerSectionRegistry::routeKey($section);

                    Route::get($uri, [SectionController::class, 'index'])
                        ->defaults('section', $section)
                        ->name($routeKey . '.index');

                    Route::get($uri . '/create', [SectionController::class, 'create'])
                        ->defaults('section', $section)
                        ->name($routeKey . '.create');

                    Route::post($uri, [SectionController::class, 'store'])
                        ->defaults('section', $section)
                        ->name($routeKey . '.store');

                    Route::get($uri . '/{id}/edit', [SectionController::class, 'edit'])
                        ->defaults('section', $section)
                        ->whereNumber('id')
                        ->name($routeKey . '.edit');

                    Route::put($uri . '/{id}', [SectionController::class, 'update'])
                        ->defaults('section', $section)
                        ->whereNumber('id')
                        ->name($routeKey . '.update');

                    Route::delete($uri . '/{id}', [SectionController::class, 'destroy'])
                        ->defaults('section', $section)
                        ->whereNumber('id')
                        ->name($routeKey . '.destroy');
                }

                Route::get('/roadmap-items/create', [SectionController::class, 'create'])->defaults('section', 'roadmap-items')->name('roadmap_items.create');
                Route::post('/roadmap-items', [SectionController::class, 'store'])->defaults('section', 'roadmap-items')->name('roadmap_items.store');
                Route::get('/roadmap-items/{id}/edit', [SectionController::class, 'edit'])->defaults('section', 'roadmap-items')->whereNumber('id')->name('roadmap_items.edit');
                Route::put('/roadmap-items/{id}', [SectionController::class, 'update'])->defaults('section', 'roadmap-items')->whereNumber('id')->name('roadmap_items.update');
                Route::delete('/roadmap-items/{id}', [SectionController::class, 'destroy'])->defaults('section', 'roadmap-items')->whereNumber('id')->name('roadmap_items.destroy');

                Route::get('/tasks/create', [SectionController::class, 'create'])->defaults('section', 'tasks')->name('tasks.create');
                Route::post('/tasks', [SectionController::class, 'store'])->defaults('section', 'tasks')->name('tasks.store');
                Route::get('/tasks/{id}/edit', [SectionController::class, 'edit'])->defaults('section', 'tasks')->whereNumber('id')->name('tasks.edit');
                Route::put('/tasks/{id}', [SectionController::class, 'update'])->defaults('section', 'tasks')->whereNumber('id')->name('tasks.update');
                Route::delete('/tasks/{id}', [SectionController::class, 'destroy'])->defaults('section', 'tasks')->whereNumber('id')->name('tasks.destroy');
            });
    });
