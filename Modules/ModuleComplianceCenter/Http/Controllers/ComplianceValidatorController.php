<?php

namespace Modules\ModuleComplianceCenter\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Modules\ModuleComplianceCenter\Http\Controllers\Concerns\PreparesComplianceCenterPage;
use Modules\ModuleComplianceCenter\Models\ComplianceValidator;
use Modules\ModuleComplianceCenter\Services\ComplianceValidatorRegistry;

class ComplianceValidatorController extends Controller
{
    use PreparesComplianceCenterPage;

    public function index(ComplianceValidatorRegistry $registry)
    {
        $registry->sync();
        $this->prepareCompliancePage('Validators', ['Validators'], [
            $this->actionLink('back', 'Back', 'fa-solid fa-angle-left', 'module_compliance_center.dashboard'),
        ]);

        return view('module-compliance-center::validators.index', [
            'validators' => ComplianceValidator::orderBy('validator_key')->get(),
        ]);
    }

    public function sync(ComplianceValidatorRegistry $registry): RedirectResponse
    {
        $registry->sync();

        return back()->with('success', 'Validator registry synchronized.');
    }

    public function enable(ComplianceValidator $validator): RedirectResponse
    {
        $validator->update(['is_enabled' => true, 'status' => $validator->is_available ? 'available' : 'unavailable']);

        return back()->with('success', 'Validator enabled.');
    }

    public function disable(ComplianceValidator $validator): RedirectResponse
    {
        $validator->update(['is_enabled' => false, 'status' => 'disabled']);

        return back()->with('success', 'Validator disabled.');
    }
}
