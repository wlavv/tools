<?php

use Illuminate\Support\Facades\Route;
use Modules\AIConsensus\Http\Controllers\AIConsensusController;

Route::middleware(['web', 'auth'])->prefix('ai-consensus')->name('ai_consensus.')->group(function () {
    Route::get('/', [AIConsensusController::class, 'index'])->name('index');
    Route::get('/create', [AIConsensusController::class, 'create'])->name('create');
    Route::post('/', [AIConsensusController::class, 'store'])->name('store');
    Route::get('/{run}', [AIConsensusController::class, 'show'])->name('show');
    Route::get('/{run}/edit', [AIConsensusController::class, 'edit'])->name('edit');
    Route::match(['put', 'patch'], '/{run}', [AIConsensusController::class, 'update'])->name('update');
    Route::delete('/{run}', [AIConsensusController::class, 'destroy'])->name('destroy');

    Route::post('/settings/credentials', [AIConsensusController::class, 'saveCredentials'])->name('credentials.save');
    Route::post('/{run}/reprocess', [AIConsensusController::class, 'reprocess'])->name('reprocess');
});
