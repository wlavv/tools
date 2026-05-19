<?php

namespace Modules\IdeaLab\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIdeaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description_raw' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:idealab_categories,id'],
            'status' => ['required', 'string', 'max:50'],
            'priority' => ['required', 'string', 'max:50'],
            'source' => ['required', 'string', 'max:80'],
            'tags' => ['nullable', 'string'],
        ];
    }
}
