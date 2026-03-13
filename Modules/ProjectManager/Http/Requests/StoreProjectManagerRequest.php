<?php
namespace Modules\ProjectManager\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectManagerRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:191'],
            'slug' => ['nullable','string','max:191'],
            'status' => ['required','string', Rule::in(array_values(config('project-manager.project_statuses', [])))],
            'priority' => ['nullable','integer','min:0'],
            'id_parent' => ['nullable','integer','min:0'],
            'description' => ['nullable','string'],
            'logo' => ['nullable','string','max:1000'],
            'url' => ['nullable','string','max:500'],
            'website' => ['nullable','string','max:500'],
            'repository_url' => ['nullable','string','max:500'],
            'documentation_url' => ['nullable','string','max:500'],
            'staging_url' => ['nullable','string','max:500'],
            'production_url' => ['nullable','string','max:500'],
            'server_notes' => ['nullable','string'],
            'deployment_notes' => ['nullable','string'],
            'business_model' => ['nullable','string','max:191'],
            'market_scope' => ['nullable','string','max:191'],
            'owner_name' => ['nullable','string','max:191'],
            'owner_email' => ['nullable','string','max:191'],
            'contact_name' => ['nullable','string','max:191'],
            'contact_email' => ['nullable','string','max:191'],
            'contact_phone' => ['nullable','string','max:50'],
            'primary_color' => ['nullable','string','max:20'],
            'secondary_color' => ['nullable','string','max:20'],
            'accent_color' => ['nullable','string','max:20'],
            'font_family' => ['nullable','string','max:120'],
            'brand_notes' => ['nullable','string'],
            'team_json' => ['nullable','string'],
            'team_notes' => ['nullable','string'],
            'structure_notes' => ['nullable','string'],
            'documentation_notes' => ['nullable','string'],
            'goals' => ['nullable','string'],
            'risks' => ['nullable','string'],
            'next_steps' => ['nullable','string'],
            'budget_notes' => ['nullable','string'],
            'start_date' => ['nullable','date'],
            'deadline' => ['nullable','date'],
        ];
    }
}
