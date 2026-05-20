<?php

use Illuminate\Support\Facades\Route;
use Modules\Calendar\Http\Controllers\CalendarController;

Route::middleware(['web', 'auth'])->prefix('hr/calendar')->name('calendar.')->group(function () {
    Route::get('/', [CalendarController::class, 'index'])->name('index');
    Route::get('/tablet/{context?}', [CalendarController::class, 'tablet'])->name('tablet');
    Route::get('/feed/{context?}', [CalendarController::class, 'feed'])->name('feed');

    Route::get('/contexts', [CalendarController::class, 'contexts'])->name('contexts.index');
    Route::post('/contexts', [CalendarController::class, 'storeContext'])->name('contexts.store');
    Route::post('/contexts/{context}', [CalendarController::class, 'updateContext'])->name('contexts.update');
    Route::post('/contexts/{context}/delete', [CalendarController::class, 'deleteContext'])->name('contexts.delete');

    Route::get('/categories', [CalendarController::class, 'categories'])->name('categories.index');
    Route::post('/categories', [CalendarController::class, 'storeCategory'])->name('categories.store');
    Route::post('/categories/{category}', [CalendarController::class, 'updateCategory'])->name('categories.update');
    Route::post('/categories/{category}/delete', [CalendarController::class, 'deleteCategory'])->name('categories.delete');

    Route::get('/events', [CalendarController::class, 'events'])->name('events.index');
    Route::get('/events/create', [CalendarController::class, 'createEvent'])->name('events.create');
    Route::post('/events', [CalendarController::class, 'storeEvent'])->name('events.store');
    Route::get('/events/{event}', [CalendarController::class, 'showEvent'])->name('events.show');
    Route::post('/events/{event}', [CalendarController::class, 'updateEvent'])->name('events.update');
    Route::post('/events/{event}/delete', [CalendarController::class, 'deleteEvent'])->name('events.delete');
});
