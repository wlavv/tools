<?php

namespace Modules\WebCatalogue\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
            'id_store' => ['nullable', 'integer', 'exists:wc_stores,id'],
        ];
    }
}
