<?php

namespace Modules\LSG\SiteManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siteId = $this->route('site')?->id;

        return [
            'name' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:lsg_sites,slug,' . ($siteId ?: 'NULL')],
            'site_type' => ['required', 'in:store,service,presentation'],
            'domain' => ['nullable', 'string', 'max:180'],
            'public_url' => ['nullable', 'string', 'max:255'],
            'environment' => ['required', 'in:production,staging,development'],
            'status' => ['required', 'in:active,inactive,maintenance,archived'],
            'default_language' => ['nullable', 'string', 'max:10'],
            'default_currency' => ['nullable', 'string', 'size:3'],
            'project_id' => ['nullable', 'integer'],
            'monitor_pagespeed' => ['nullable', 'boolean'],
            'monitor_availability' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->input('slug') ?: Str::slug((string) $this->input('name')),
            'default_language' => $this->input('default_language') ?: 'pt',
            'default_currency' => strtoupper((string) ($this->input('default_currency') ?: 'EUR')),
            'monitor_pagespeed' => $this->boolean('monitor_pagespeed'),
            'monitor_availability' => $this->boolean('monitor_availability'),
        ]);
    }
}
