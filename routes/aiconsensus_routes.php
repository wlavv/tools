<?php

use App\Modules\AiConsensus\Http\Controllers\AiConsensusController;
use Illuminate\Support\Facades\Route;

Route::prefix('aiconsensus')->middleware(['web','auth'])->group(function () {
    Route::get('/', [AiConsensusController::class, 'index'])->name('aiconsensus.index');
    Route::get('/create', [AiConsensusController::class, 'create'])->name('aiconsensus.create');
    Route::post('/', [AiConsensusController::class, 'store'])->name('aiconsensus.store');
    Route::get('/usage', [AiConsensusController::class, 'usage'])->name('aiconsensus.usage');
    Route::get('/{id}', [AiConsensusController::class, 'show'])->name('aiconsensus.show');
    Route::get('/{id}/status', [AiConsensusController::class, 'status'])->name('aiconsensus.status'); // optional
});