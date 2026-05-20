<?php

namespace Modules\ModuleDesignValidator\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleDesignValidator\Services\ModuleDesignValidatorService;

class ModuleDesignValidatorController extends Controller
{
    public function index()
    {
        $this->setPageTitle(__('module-design-validator::messages.title'));
        $this->setBreadcrumbs([
            ['label' => 'Dashboard', 'url' => route('dashboard.index'), 'translate' => false],
            ['label' => __('module-design-validator::messages.title'), 'url' => null, 'translate' => false],
        ]);
        $this->setActions([
            [
                'key' => 'back',
                'label' => 'Back',
                'icon' => 'fa-solid fa-angle-left',
                'url' => route('dashboard.index'),
                'type' => 'link',
            ],
        ]);

        return $this->view('module-design-validator::index');
    }

    public function run(Request $request, ModuleDesignValidatorService $validator)
    {
        $validated = $request->validate([
            'module_name' => ['required', 'string', 'max:120'],
            'module_path' => ['required', 'string', 'max:500'],
        ]);

        $modulePath = $validated['module_path'];
        if (! str_starts_with($modulePath, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $modulePath)) {
            $modulePath = base_path($modulePath);
        }

        $context = new ModuleValidationContext(
            moduleName: $validated['module_name'],
            modulePath: $modulePath,
            sourceType: 'manual_design_validation',
            requestedBy: auth()->id(),
        );

        $result = $validator->validate($context);

        $this->setPageTitle(__('module-design-validator::messages.result'));
        $this->setBreadcrumbs([
            ['label' => 'Dashboard', 'url' => route('dashboard.index'), 'translate' => false],
            ['label' => __('module-design-validator::messages.title'), 'url' => route('module-design-validator.index'), 'translate' => false],
            ['label' => __('module-design-validator::messages.result'), 'url' => null, 'translate' => false],
        ]);
        $this->setActions([
            [
                'key' => 'back',
                'label' => 'Back',
                'icon' => 'fa-solid fa-angle-left',
                'url' => route('module-design-validator.index'),
                'type' => 'link',
            ],
        ]);

        return $this->view('module-design-validator::result', [
            'result' => $result,
            'resultArray' => $result->toArray(),
        ]);
    }
}
