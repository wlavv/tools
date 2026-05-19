<?php

use Illuminate\Support\Facades\Route;
use Modules\AIConsensus\Http\Controllers\AIConsensusApiController;

Route::middleware(config('ai_consensus.middleware', ['web', 'auth']))
    ->prefix('ai-consensus/api')
    ->name('api.ai_consensus.')
    ->group(function () {
        Route::post('/runs', [AIConsensusApiController::class, 'storeRun'])->name('runs.store');
    });
