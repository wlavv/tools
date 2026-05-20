<?php

namespace Modules\ModuleComplianceCenter\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectComplianceRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
