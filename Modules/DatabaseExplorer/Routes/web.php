<?php

use Illuminate\Support\Facades\Route;
use Modules\DatabaseExplorer\Http\Controllers\DatabaseExplorerController;

Route::middleware(config('database-explorer.middleware', ['web', 'auth']))
    ->prefix(config('database-explorer.route_prefix', 'database-explorer'))
    ->name('database_explorer.')
    ->group(function () {
        Route::get('/', [DatabaseExplorerController::class, 'index'])->name('index');
        Route::get('/health', [DatabaseExplorerController::class, 'health'])->name('health');
        Route::get('/snapshots', [DatabaseExplorerController::class, 'snapshots'])->name('snapshots');
        Route::post('/snapshots/collect', [DatabaseExplorerController::class, 'collectSnapshot'])->name('snapshots.collect');
        Route::get('/tables/{schemaName}/{tableName}', [DatabaseExplorerController::class, 'show'])
            ->where(['schemaName' => '[A-Za-z_][A-Za-z0-9_]*', 'tableName' => '[A-Za-z_][A-Za-z0-9_]*'])
            ->name('show');
    });
