<?php

namespace Modules\AIConsensus\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAIConsensusRequest extends FormRequest
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
            'status' => ['nullable', 'in:queued,running,integrating,done,failed'],
            'options' => ['nullable', 'array'],
            'files' => ['nullable', 'array', 'max:' . (int) config('ai_consensus.storage.max_files', 10)],
            'files.*' => ['file', 'max:' . (int) config('ai_consensus.storage.max_file_size_kb', 10240)],
        ];
    }
}
