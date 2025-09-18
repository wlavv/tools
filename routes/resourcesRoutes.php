<?php

use App\Http\Controllers\Areas\{
    dashboardController,
    adminController,
    webController,
    hrController,
    financeController,
    marketingController,
    customerSupportController,
    salesController
};

Route::resources([
    'home'            => dashboardController::class,
    'dashboard'       => dashboardController::class,
    'administration'  => adminController::class,
    'web'             => webController::class,
    'hr'              => hrController::class,
    'finance'         => financeController::class,
    'marketing'       => marketingController::class,
    'customerSupport' => customerSupportController::class,
    'sales'           => salesController::class,
]);

