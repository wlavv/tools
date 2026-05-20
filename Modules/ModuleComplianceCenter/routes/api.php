<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\ModuleComplianceCenter\Models\ComplianceManagedModule;
use Modules\ModuleComplianceCenter\Models\ComplianceRun;
use Modules\ModuleComplianceCenter\Models\ComplianceValidator;
use Modules\ModuleComplianceCenter\Services\ModuleComplianceCenterGateway;

Route::middleware(['api'])
    ->prefix(config('module-compliance-center.routes.api_prefix', 'api/module-compliance-center'))
    ->group(function () {
        Route::post('runs', function (Request $request, ModuleComplianceCenterGateway $gateway) {
            $run = $gateway->run(array_merge($request->all(), [
                'requested_by' => optional($request->user())->id,
            ]));

            return response()->json($run->fresh(['validators', 'results', 'report']), 201);
        });

        Route::get('runs/{uuid}', function (string $uuid) {
            return ComplianceRun::where('uuid', $uuid)->with(['validators', 'results', 'report'])->firstOrFail();
        });

        Route::get('modules', fn () => ComplianceManagedModule::orderBy('module_name')->get());
        Route::get('validators', fn () => ComplianceValidator::orderBy('validator_key')->get());
    });
