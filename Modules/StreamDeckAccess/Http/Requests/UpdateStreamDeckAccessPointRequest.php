<?php

namespace Modules\StreamDeckAccess\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\StreamDeckAccess\Models\StreamDeckAccessPoint;

class UpdateStreamDeckAccessPointRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('payload_json')) {
            $payloadJson = trim((string) $this->input('payload_json'));
            $data['payload'] = $payloadJson === '' ? null : json_decode($payloadJson, true);
        }

        if ($this->has('allowed_ips_text')) {
            $data['allowed_ips'] = $this->parseLines((string) $this->input('allowed_ips_text'));
        }

        if (! $this->isJson()) {
            $data['enabled'] = $this->boolean('enabled');
            $data['respond_json'] = $this->boolean('respond_json');
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('payload_json')) {
                $payloadJson = trim((string) $this->input('payload_json'));

                if ($payloadJson !== '') {
                    json_decode($payloadJson, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $validator->errors()->add('payload_json', 'O payload deve ser JSON válido.');
                    }
                }
            }
        });
    }

    public function rules(): array
    {
        $accessPoint = $this->route('accessPoint');
        $id = $accessPoint instanceof StreamDeckAccessPoint ? $accessPoint->id : null;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:160',
                'regex:/^[a-z0-9][a-z0-9._-]*$/',
                Rule::unique('streamdeck_access_points', 'slug')->ignore($id),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => ['sometimes', 'required', Rule::in(array_keys(config('streamdeck-access.types', [])))],
            'enabled' => ['sometimes', 'boolean'],
            'target_url' => [
                'nullable',
                'required_if:type,redirect',
                'string',
                'max:2048',
                'regex:/^(https?:\/\/|\/(?!\/))\S+$/i',
            ],
            'task_key' => ['nullable', 'required_if:type,task', Rule::in(array_keys(config('streamdeck-access.tasks', [])))],
            'payload' => ['nullable', 'array'],
            'payload_json' => ['nullable', 'string'],
            'allowed_ips' => ['nullable', 'array'],
            'allowed_ips.*' => ['required', 'string', 'max:80'],
            'allowed_ips_text' => ['nullable', 'string'],
            'expires_at' => ['nullable', 'date'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'cooldown_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'queue' => ['nullable', 'string', 'max:120'],
            'respond_json' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'target_url.regex' => 'O destino deve ser um URL http(s) ou um caminho interno iniciado por /.',
            'slug.regex' => 'O slug só pode conter letras minúsculas, números, pontos, underscores e hífens.',
        ];
    }

    protected function parseLines(string $value): array
    {
        $items = preg_split('/[\r\n,]+/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $items)));
    }
}
