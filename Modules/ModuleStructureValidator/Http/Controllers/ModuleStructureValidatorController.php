<?php

namespace Modules\ModuleStructureValidator\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleStructureValidator\Services\ModuleStructureValidatorService;

class ModuleStructureValidatorController extends Controller
{
    public function index()
    {
        return view('module-structure-validator::index');
    }

    public function run(Request $request, ModuleStructureValidatorService $validator)
    {
        $validated = $request->validate([
            'module_name' => ['required', 'string', 'max:120'],
            'module_path' => ['required', 'string', 'max:500'],
        ]);

        $result = $validator->validate(ModuleValidationContext::fromArray($validated));

        return view('module-structure-validator::result', [
            'result' => $result->toArray(),
            'moduleName' => $validated['module_name'],
        ]);
    }
}
