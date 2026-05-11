<?php

namespace Modules\DocumentManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'workspace_id' => ['nullable', 'integer'],
            'folder_id' => ['nullable', 'integer'],
            'category_id' => ['nullable', 'integer'],
            'document_type' => ['nullable', 'string', 'max:120'],
            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer'],
            'metadata' => ['nullable', 'array'],
            'metadata.document_value' => ['nullable', 'numeric', 'min:0'],
            'metadata.currency' => ['nullable', 'string', 'max:12'],
            'metadata.payment_status' => ['nullable', 'string', 'max:80'],
            'metadata.paid_at' => ['nullable', 'date'],
            'metadata.paid_by' => ['nullable', 'string', 'max:255'],
            'metadata.payment_method' => ['nullable', 'string', 'max:120'],
            'metadata.payment_reference' => ['nullable', 'string', 'max:255'],
            'metadata.operational_notes' => ['nullable', 'string'],
        ];
    }
}
