<?php

namespace Modules\AIConsensus\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAIConsensusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120'],
            'template_key' => ['nullable', 'string', 'max:80'],
            'prompt' => ['required', 'string'],
            'files' => ['nullable', 'array', 'max:' . (int) config('ai_consensus.storage.max_files', 10)],
            'files.*' => ['file', 'max:' . (int) config('ai_consensus.storage.max_file_size_kb', 10240)],
            'options' => ['nullable', 'array'],
            'options.include_files' => ['nullable', 'boolean'],
            'options.run_claude' => ['nullable', 'boolean'],
            'options.run_gemini' => ['nullable', 'boolean'],
            'options.run_openai_final' => ['nullable', 'boolean'],
        ];
    }
}
