<?php

use Illuminate\Support\Facades\Route;
use Modules\Investments\Http\Controllers\AssetController;
use Modules\Investments\Http\Controllers\BrokerAccountController;
use Modules\Investments\Http\Controllers\DashboardController;
use Modules\Investments\Http\Controllers\PositionController;

Route::middleware(config('investments.middleware', ['web', 'auth']))
    ->prefix(config('investments.route_prefix', 'investments'))
    ->name('investments.')
    ->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        Route::resource('assets', AssetController::class)->only(['index', 'create', 'store']);

        Route::resource('broker-accounts', BrokerAccountController::class)
            ->only(['index', 'create', 'store', 'edit', 'update'])
            ->names('broker_accounts');

        Route::post('broker-accounts/{brokerAccount}/ibkr/test', [BrokerAccountController::class, 'testIbkr'])->name('broker_accounts.ibkr.test');
        Route::post('broker-accounts/{brokerAccount}/ibkr/sync', [BrokerAccountController::class, 'syncIbkr'])->name('broker_accounts.ibkr.sync');
        Route::post('broker-accounts/{brokerAccount}/ibkr/select', [BrokerAccountController::class, 'selectIbkrAccount'])->name('broker_accounts.ibkr.select');

        Route::resource('positions', PositionController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
        Route::post('positions/{position}/simulate-step', [PositionController::class, 'simulateStep'])->name('positions.simulate_step');
    });
