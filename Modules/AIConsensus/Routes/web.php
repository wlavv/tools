<?php

use Illuminate\Support\Facades\Route;
use Modules\AIConsensus\Http\Controllers\AIConsensusController;
use Modules\AIConsensus\Http\Controllers\AIConsensusLogController;
use Modules\AIConsensus\Http\Controllers\AIConsensusProviderController;
use Modules\AIConsensus\Http\Controllers\AIConsensusRunController;
use Modules\AIConsensus\Http\Controllers\AIConsensusTemplateController;

Route::middleware(config('ai_consensus.middleware', ['web', 'auth']))
    ->prefix(config('ai_consensus.route_prefix', 'ai-consensus'))
    ->name('ai_consensus.')
    ->group(function () {
    Route::get('/', [AIConsensusController::class, 'index'])->name('index');
    Route::get('/legacy', [AIConsensusController::class, 'legacy'])->name('legacy.index');
    Route::get('/runs', [AIConsensusRunController::class, 'index'])->name('runs.index');
    Route::get('/runs/create', [AIConsensusRunController::class, 'create'])->name('runs.create');
    Route::post('/runs', [AIConsensusRunController::class, 'store'])->name('runs.store');
    Route::post('/runs/{run}/process', [AIConsensusRunController::class, 'process'])->name('runs.process');
    Route::get('/runs/{run}/download', [AIConsensusRunController::class, 'download'])->name('runs.download');
    Route::post('/runs/{run}/module-package', [AIConsensusRunController::class, 'modulePackage'])->name('runs.module_package');
    Route::get('/runs/{run}', [AIConsensusRunController::class, 'show'])->name('runs.show');

    Route::get('/templates', [AIConsensusTemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/create', [AIConsensusTemplateController::class, 'create'])->name('templates.create');
    Route::post('/templates', [AIConsensusTemplateController::class, 'store'])->name('templates.store');
    Route::get('/templates/{template}/edit', [AIConsensusTemplateController::class, 'edit'])->name('templates.edit');
    Route::match(['put', 'patch'], '/templates/{template}', [AIConsensusTemplateController::class, 'update'])->name('templates.update');

    Route::get('/providers', [AIConsensusProviderController::class, 'index'])->name('providers.index');
    Route::match(['put', 'patch'], '/providers/{provider}', [AIConsensusProviderController::class, 'update'])->name('providers.update');

    Route::get('/logs', [AIConsensusLogController::class, 'index'])->name('logs.index');

    Route::get('/create', [AIConsensusController::class, 'create'])->name('create');
    Route::post('/', [AIConsensusController::class, 'store'])->name('store');
    Route::get('/{run}', [AIConsensusController::class, 'show'])->name('show');
    Route::get('/{run}/edit', [AIConsensusController::class, 'edit'])->name('edit');
    Route::match(['put', 'patch'], '/{run}', [AIConsensusController::class, 'update'])->name('update');
    Route::delete('/{run}', [AIConsensusController::class, 'destroy'])->name('destroy');

    Route::post('/settings/credentials', [AIConsensusController::class, 'saveCredentials'])->name('credentials.save');
    Route::post('/{run}/reprocess', [AIConsensusController::class, 'reprocess'])->name('reprocess');
});
