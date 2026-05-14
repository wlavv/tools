<?php

namespace Modules\WebCatalogue\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmImportBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
