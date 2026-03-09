<?php

namespace Modules\AssetLibrary\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetLibraryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:180'],
            'type' => ['required', Rule::in(['image', 'model', 'video', 'texture', 'hdri', 'document', 'other'])],
            'status' => ['required', Rule::in(['draft', 'active', 'archived'])],
            'description' => ['nullable', 'string'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:255'],
            'is_public' => ['nullable', 'boolean'],
            'file' => ['nullable', 'file', 'max:51200'],
        ];
    }
}
