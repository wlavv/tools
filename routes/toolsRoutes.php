<?php


//Use App\Http\Controllers\CustomTools\uploadsController;
Use App\Http\Controllers\CustomTools\oriflameController;
Use App\Http\Controllers\CustomTools\customersController;

//Route::post( 'customTools/uploads/upload',              [uploadsController::class, 'upload'])->name('uploads.upload');

//Route::resources([ 'customTools/uploads'=>              uploadsController::class]);
Route::resources([ 'sales/oriflame'=>                   oriflameController::class]);
Route::resources([ 'sales/oriflame/order/new'=>         oriflameController::class]);
Route::post( 'sales/oriflame/order/show',               [oriflameController::class, 'displayOrder'])->name('oriflame.displayOrder');
Route::post( 'sales/oriflame/order/getCustomer',        [oriflameController::class, 'getCustomerInfo'])->name('oriflame.getCustomerInfo');
Route::post( 'sales/oriflame/order/product',            [oriflameController::class, 'getProductInfo'])->name('oriflame.getProductInfo');


Route::resources([ 'sales/customers'=>                  customersController::class]);
Route::post( 'sales/customers/active',                  [customersController::class, 'active'])->name('customers.active');
Route::put('sales/customers/{id}',                      [customersController::class, 'update']);