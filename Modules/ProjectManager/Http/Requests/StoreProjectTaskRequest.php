<?php

namespace Modules\ProjectManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:191'],
            'status' => ['required', 'string', Rule::in(array_values(config('project-manager.task_statuses', [])))],
            'priority' => ['nullable', 'integer', 'min:0'],
            'id_parent' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'expected_time' => ['nullable', 'integer', 'min:0'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
