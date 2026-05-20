<?php

namespace Modules\ModuleIntegrationValidator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleIntegrationValidator\Services\ModuleIntegrationValidatorService;

class ModuleIntegrationValidatorController extends Controller
{
    public function index()
    {
        return view('module-integration-validator::index.index', [
            'defaultPath' => config('module-integration-validator.default_module_base_path'),
            'result' => null,
        ]);
    }

    public function run(Request $request, ModuleIntegrationValidatorService $validator)
    {
        $data = $request->validate([
            'module_name' => ['required', 'string', 'max:120'],
            'module_path' => ['required', 'string', 'max:500'],
        ]);

        $context = new ModuleValidationContext(
            moduleName: $data['module_name'],
            modulePath: $data['module_path'],
            metadata: [
                'source' => 'manual_ui',
                'requested_by' => optional($request->user())->id,
            ]
        );

        $result = $validator->validate($context);

        return view('module-integration-validator::index.index', [
            'defaultPath' => config('module-integration-validator.default_module_base_path'),
            'result' => $result,
            'moduleName' => $data['module_name'],
            'modulePath' => $data['module_path'],
        ]);
    }
}
