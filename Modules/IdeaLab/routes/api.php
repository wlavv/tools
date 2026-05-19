<?php

use Illuminate\Support\Facades\Route;
use Modules\IdeaLab\Http\Controllers\Api\IdeaLabApiController;

Route::middleware(['api'])
    ->prefix('api/idealab')
    ->name('api.idealab.')
    ->group(function () {
        Route::get('/ideas/{idea}/ai-payload', [IdeaLabApiController::class, 'payload'])->name('ideas.ai-payload');
    });
