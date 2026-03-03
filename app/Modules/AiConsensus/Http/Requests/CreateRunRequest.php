<?php

namespace App\Modules\AiConsensus\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajusta se tiveres RBAC
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable','string','max:120'],
            'template_key' => ['required','string','max:80'],
            'prompt' => ['required','string','min:10'],
        ];
    }
}