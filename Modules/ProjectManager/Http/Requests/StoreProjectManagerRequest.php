<?php

namespace Modules\ProjectManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectManagerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:191'],
            'slug' => ['nullable', 'string', 'max:191'],
            'status' => ['required', 'string', Rule::in(array_values(config('project-manager.project_statuses', [])))],
            'priority' => ['nullable', 'integer', 'min:0'],
            'id_parent' => ['nullable', 'integer', 'min:0'],
            'url' => ['nullable', 'url', 'max:500'],
            'logo' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'font_family' => ['nullable', 'string', 'max:120'],
            'brand_notes' => ['nullable', 'string'],
            'contact_name' => ['nullable', 'string', 'max:191'],
            'contact_email' => ['nullable', 'email', 'max:191'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'url', 'max:500'],
            'social_facebook' => ['nullable', 'url', 'max:500'],
            'social_instagram' => ['nullable', 'url', 'max:500'],
            'social_linkedin' => ['nullable', 'url', 'max:500'],
            'social_youtube' => ['nullable', 'url', 'max:500'],
            'repository_url' => ['nullable', 'url', 'max:500'],
            'documentation_url' => ['nullable', 'url', 'max:500'],
            'team_notes' => ['nullable', 'string'],
            'team_json' => ['nullable', 'string'],
            'structure_notes' => ['nullable', 'string'],
            'documentation_notes' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
        ];
    }
}
