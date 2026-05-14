<?php

use Illuminate\Support\Facades\Route;
use Modules\ErrorCenter\Http\Controllers\ErrorCenterController;

$prefix = trim((string) config('error-center.route_prefix', 'admin/error-center'), '/');
$namePrefix = (string) config('error-center.route_name_prefix', 'error-center.');

Route::prefix($prefix)
    ->as($namePrefix)
    ->middleware(config('error-center.view_middleware', ['web', 'auth']))
    ->group(function (): void {
        Route::get('/', [ErrorCenterController::class, 'index'])->name('index');

        Route::prefix('api')->as('api.')->group(function (): void {
            Route::get('/stats', [ErrorCenterController::class, 'stats'])->name('stats');
            Route::get('/events', [ErrorCenterController::class, 'events'])->name('events');
            Route::get('/events/{errorEvent}', [ErrorCenterController::class, 'eventDetail'])->name('events.show');
            Route::get('/events/{errorEvent}/occurrences', [ErrorCenterController::class, 'occurrences'])->name('events.occurrences');

            Route::middleware(config('error-center.manage_middleware', ['web', 'auth']))->group(function (): void {
                Route::post('/events/{errorEvent}/status', [ErrorCenterController::class, 'updateStatus'])->name('events.status');
                Route::post('/events/{errorEvent}/resolve', [ErrorCenterController::class, 'resolve'])->name('events.resolve');
                Route::post('/events/{errorEvent}/ignore', [ErrorCenterController::class, 'ignore'])->name('events.ignore');
            });
        });

        Route::get('/{errorEvent}', [ErrorCenterController::class, 'show'])
            ->whereNumber('errorEvent')
            ->name('show');
    });
