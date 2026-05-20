<?php

namespace Modules\ModuleSecurityValidator\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\ModuleComplianceCore\DTO\ModuleValidationContext;
use Modules\ModuleSecurityValidator\Services\ModuleSecurityValidatorService;

class ModuleSecurityValidatorController extends Controller
{
    public function index()
    {
        $this->setPageTitle(__('module-security-validator::messages.title'));
        $this->setBreadcrumbs([
            ['label' => 'Dashboard', 'url' => route('dashboard.index'), 'translate' => false],
            ['label' => __('module-security-validator::messages.title'), 'url' => null, 'translate' => false],
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

        return $this->view('module-security-validator::index');
    }

    public function run(Request $request, ModuleSecurityValidatorService $validator)
    {
        $validated = $request->validate([
            'module_name' => ['required', 'string', 'max:120'],
            'module_path' => ['required', 'string', 'max:500'],
        ]);

        $modulePath = $validated['module_path'];
        if (! str_starts_with($modulePath, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\/', $modulePath)) {
            $modulePath = base_path($modulePath);
        }

        $context = new ModuleValidationContext(
            moduleName: $validated['module_name'],
            modulePath: $modulePath,
            sourceType: 'manual_security_validation',
            requestedBy: auth()->id(),
        );

        $result = $validator->validate($context);

        $this->setPageTitle(__('module-security-validator::messages.result'));
        $this->setBreadcrumbs([
            ['label' => 'Dashboard', 'url' => route('dashboard.index'), 'translate' => false],
            ['label' => __('module-security-validator::messages.title'), 'url' => route('module-security-validator.index'), 'translate' => false],
            ['label' => __('module-security-validator::messages.result'), 'url' => null, 'translate' => false],
        ]);
        $this->setActions([
            [
                'key' => 'back',
                'label' => 'Back',
                'icon' => 'fa-solid fa-angle-left',
                'url' => route('module-security-validator.index'),
                'type' => 'link',
            ],
        ]);

        return $this->view('module-security-validator::result', [
            'result' => $result,
            'resultArray' => $result->toArray(),
        ]);
    }
}
