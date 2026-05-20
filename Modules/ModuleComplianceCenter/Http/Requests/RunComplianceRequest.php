<?php

namespace Modules\ModuleComplianceCenter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunComplianceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'managed_module_id' => ['nullable', 'integer', 'exists:module_compliance_center_modules,id'],
            'module_name' => ['required_without:managed_module_id', 'string', 'max:120'],
            'module_path' => ['required_without:managed_module_id', 'string', 'max:500'],
            'source_type' => ['nullable', 'string', 'max:80'],
            'source_id' => ['nullable', 'string', 'max:120'],
            'validators' => ['nullable', 'array'],
            'validators.*' => ['string'],
            'async' => ['nullable', 'boolean'],
            'generate_report' => ['nullable', 'boolean'],
        ];
    }
}
