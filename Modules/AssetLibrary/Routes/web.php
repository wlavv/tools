<?php

use Illuminate\Support\Facades\Route;
use Modules\AssetLibrary\Http\Controllers\AssetLibraryController;

Route::middleware(['web'])
    ->prefix('asset-library')
    ->name('asset_library.')
    ->group(function () {
        Route::get('/', [AssetLibraryController::class, 'index'])->name('index');
        Route::get('/create', [AssetLibraryController::class, 'create'])->name('create');
        Route::post('/', [AssetLibraryController::class, 'store'])->name('store');
        Route::get('/{assetLibrary}', [AssetLibraryController::class, 'show'])->name('show');
        Route::get('/{assetLibrary}/edit', [AssetLibraryController::class, 'edit'])->name('edit');
        Route::put('/{assetLibrary}', [AssetLibraryController::class, 'update'])->name('update');
        Route::delete('/{assetLibrary}', [AssetLibraryController::class, 'destroy'])->name('destroy');
        Route::get('/{assetLibrary}/download/file', [AssetLibraryController::class, 'download'])->name('download');
    });
