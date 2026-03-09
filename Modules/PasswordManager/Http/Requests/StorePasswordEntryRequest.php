<?php

namespace Modules\PasswordManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePasswordEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'category' => ['nullable', 'string', 'max:100'],
            'url' => ['nullable', 'url', 'max:255'],
            'account_email' => ['nullable', 'email', 'max:150'],
            'login_username' => ['nullable', 'string', 'max:150'],
            'password' => ['required', 'string', 'max:255'],
            'secret' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_favorite' => ['nullable', 'boolean'],
        ];
    }
}
