<?php

namespace Modules\AIConsensus\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAIConsensusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->filled('title') ? trim((string) $this->input('title')) : null,
            'template_key' => $this->filled('template_key') ? trim((string) $this->input('template_key')) : null,
            'options' => [
                'include_files' => $this->boolean('options.include_files'),
                'run_claude' => $this->boolean('options.run_claude'),
                'run_gemini' => $this->boolean('options.run_gemini'),
                'run_openai_final' => $this->boolean('options.run_openai_final'),
            ],
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120'],
            'template_key' => ['nullable', 'string', 'max:80'],
            'prompt' => ['required', 'string'],
            'status' => ['nullable', 'in:queued,running,integrating,done,failed'],
            'options' => ['nullable', 'array'],
            'options.include_files' => ['nullable', 'boolean'],
            'options.run_claude' => ['nullable', 'boolean'],
            'options.run_gemini' => ['nullable', 'boolean'],
            'options.run_openai_final' => ['nullable', 'boolean'],
            'files' => ['nullable', 'array', 'max:' . (int) config('ai_consensus.storage.max_files', 10)],
            'files.*' => ['file', 'max:' . (int) config('ai_consensus.storage.max_file_size_kb', 10240)],
        ];
    }

    public function messages(): array
    {
        return [
            'prompt.required' => 'O prompt é obrigatório.',
            'files.max' => 'Foi excedido o número máximo de ficheiros permitidos.',
            'files.*.max' => 'Um dos ficheiros excede o tamanho máximo permitido.',
        ];
    }
}
