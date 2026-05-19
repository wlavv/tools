<?php

namespace Modules\AIConsensus\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'source_module' => ['required', 'string', 'max:80'],
            'source_type' => ['required', 'string', 'max:80'],
            'source_id' => ['nullable'],
            'template_key' => ['required', 'string', 'max:150'],
            'output_type' => ['required', 'string', 'max:80'],
            'input_payload' => ['required', 'array'],
            'options' => ['nullable', 'array'],
            'requested_by' => ['nullable', 'integer'],
            'title' => ['nullable', 'string', 'max:180'],
            'message' => ['nullable', 'string'],
        ];
    }
}
