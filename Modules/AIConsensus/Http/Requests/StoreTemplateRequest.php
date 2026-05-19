<?php

namespace Modules\AIConsensus\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_key' => ['required', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'module_scope' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:80'],
            'system_prompt' => ['nullable', 'string'],
            'user_prompt_template' => ['required', 'string'],
            'expected_output_schema' => ['nullable', 'array'],
            'default_output_type' => ['nullable', 'string', 'max:80'],
            'default_options' => ['nullable', 'array'],
            'version' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
