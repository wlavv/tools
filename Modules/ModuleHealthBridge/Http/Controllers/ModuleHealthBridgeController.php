<?php

namespace Modules\ModuleHealthBridge\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleHealthBridge\Services\ModuleHealthBridgeService;

class ModuleHealthBridgeController extends Controller
{
    public function index()
    {
        return view('module-health-bridge::index', [
            'defaultModulePath' => base_path('Modules'),
        ]);
    }

    public function run(Request $request, ModuleHealthBridgeService $validator)
    {
        $payload = $request->validate([
            'module_name' => ['required', 'string', 'max:120'],
            'module_path' => ['required', 'string', 'max:500'],
        ]);

        $context = new ModuleValidationContext(
            moduleName: $payload['module_name'],
            modulePath: $payload['module_path'],
            sourceType: 'manual_health_bridge_run',
            requestedBy: auth()->id(),
        );

        $result = $validator->validate($context);

        return view('module-health-bridge::result', [
            'result' => $result->toArray(),
            'context' => $context,
        ]);
    }
}
