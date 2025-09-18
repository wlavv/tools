<?php

//Use App\Http\Controllers\CustomTools\uploadsController;
Use App\Http\Controllers\CustomTools\oriflameController;
Use App\Http\Controllers\CustomTools\customersController;
Use App\Http\Controllers\CustomTools\budgetController;
Use App\Http\Controllers\CustomTools\tasksController;
Use App\Http\Controllers\CustomTools\groupStructureController;
Use App\Http\Controllers\CustomTools\projectDetailsController;
Use App\Http\Controllers\CustomTools\todoController;


/****************************************** BUDGET ******************************************/
Route::resources([ 'administration/budget'=>                     budgetController::class]);
Route::post( 'administration/budget/update/data',                [budgetController::class, 'updateData'])->name('budget.updateData');
Route::post( 'administration/budget/update/forecast/data',       [budgetController::class, 'updateForecastData'])->name('budget.updateForecastData');
Route::post( 'administration/budget/update/detail',              [budgetController::class, 'updateDetail'])->name('budget.updateDetail');
Route::post( 'administration/budget/delete/detail',              [budgetController::class, 'deleteDetail'])->name('budget.deleteDetail');
Route::post( 'administration/budget/objective/add',              [budgetController::class, 'addObjective'])->name('budget.addObjective');
Route::post( 'administration/budget/objective/done',             [budgetController::class, 'setObjectiveAsDone'])->name('budget.setObjectiveAsDone');

/************************************* GROUP STRUCTURE *************************************/
Route::get(  'administration/groupStructure',                    [groupStructureController::class, 'index'])->name('groupStructure.index');
Route::post( 'administration/groupStructure/remove',             [groupStructureController::class, 'destroy'])->name('groupStructure.destroy');
Route::get(  'administration/groupStructure/{id}/edit',          [groupStructureController::class, 'edit'])->name('groupStructure.edit');
Route::post( 'administration/groupStructure/new/project',        [groupStructureController::class, 'newProject'])->name('groupStructure.newProject');

/************************************* PROJECT DETAILS *************************************/
Route::get( 'administration/groupStructure/project/{id}',[projectDetailsController::class, 'index'])->name('projectDetails.index');

/************************************* PROJECT DETAILS *************************************/
Route::get( 'administration/groupStructure/todo/{id}',  [todoController::class, 'index'])->name('todo.index');
Route::post('administration/groupStructure/todo/store', [todoController::class, 'store'])->name('todo.store');
Route::post('administration/groupStructure/todo/update/order', [todoController::class, 'saveOrder'])->name('todo.saveOrder');
Route::delete('administration/groupStructure/todo/destroy/{id}', [todoController::class, 'destroy'])->name('todo.destroy');
Route::post('administration/groupStructure/todo/done/{id}', [todoController::class, 'setDone'])->name('todo.setDone');


/******************************************  TASKS ******************************************/
Route::resources([ 'tasks'=>                            tasksController::class]);
Route::post('/taks/update',                             [TasksController::class, 'updateDone'])->name('tasks.updateDone');
Route::get( '/taks/calendar/{year}/{month}',            [TasksController::class, 'getMonthInfo'])->name('tasks.getMonthInfo');


/******************************************  UPLOADS ******************************************/
//Route::post( 'customTools/uploads/upload',              [uploadsController::class, 'upload'])->name('uploads.upload');
//Route::resources([ 'customTools/uploads'=>              uploadsController::class]);


/****************************************** ORIFLAME ******************************************/
Route::resources([ 'sales/oriflame'=>                   oriflameController::class]);
Route::resources([ 'sales/oriflame/order/new'=>         oriflameController::class]);
Route::post( 'sales/oriflame/order/show',               [oriflameController::class, 'displayOrder'])->name('oriflame.displayOrder');
Route::post( 'sales/oriflame/order/product',            [oriflameController::class, 'getProductInfo'])->name('oriflame.getProductInfo');
Route::get(  'sales/oriflame/order/list/{status}',      [oriflameController::class, 'list'])->name('oriflame.list');
Route::post(  'sales/oriflame/order/change/status',     [oriflameController::class, 'changeOrderStatus'])->name('oriflame.changeOrderStatus');
Route::post( 'sales/oriflame/order/products/added',     [oriflameController::class, 'getOrderProducts'])->name('oriflame.getOrderProducts');
Route::post( 'sales/oriflame/order/products/update',    [oriflameController::class, 'updateProductQuantity'])->name('oriflame.updateProductQuantity');
Route::post( 'sales/oriflame/order/products/remove',    [oriflameController::class, 'removeProduct'])->name('oriflame.removeProduct');


/******************************************  CUSTOMER ******************************************/
Route::resources([ 'sales/customers'=>                  customersController::class]);
Route::post( 'sales/customers/getCustomer',             [customersController::class, 'getCustomerInfo'])->name('customer.getCustomerInfo');
Route::post( 'sales/customers/active',                  [customersController::class, 'active'])->name('customers.active');
Route::put('sales/customers/{id}',                      [customersController::class, 'update']);


/******************************************  READING ******************************************/
use App\Http\Controllers\ImageController;
Route::get('/camera',                                   [ImageController::class, 'camera'])->name('camera.view');
Route::post('/camera/check',                            [ImageController::class, 'cameraCheck'])->name('camera.check');
Route::get('/compare-image',                            [ImageController::class, 'compareExternalImage']);

/******************************************  MTG     ******************************************/
use App\Http\Controllers\mtg\mtgController;
Route::resources([ '/webmaster/mtg'                     => mtgController::class]);
Route::get('/webmaster/mtg/showSet/{code}/{sub_set?}',  [mtgController::class, 'showSet'])->name('mtg.showSet');
Route::get('/webmaster/mtg/front/find',                 [mtgController::class, 'findCard'])->name('mtg.findCard');
Route::post('/webmaster/mtg/front/postCardDetail',      [mtgController::class, 'postCardDetail'])->name('mtg.postCardDetail');
Route::get('/webmaster/mtg/generate/description/{id}',  [mtgController::class, 'generateDescription'])->name('mtg.generateDescription');
