<?php

namespace Modules\ModuleComplianceCenter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApproveComplianceRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
