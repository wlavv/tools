<?php

namespace Modules\IdeaLab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunAiConsensusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'template_id' => ['nullable', 'exists:idealab_ai_templates,id'],
            'message' => ['nullable', 'string'],
            'mode' => ['required', 'in:template,chat'],
        ];
    }
}
