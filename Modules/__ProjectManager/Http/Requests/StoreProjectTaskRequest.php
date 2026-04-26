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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'priority' => (int) ($this->input('priority', 3)),
            'status' => (int) ($this->input('status', 0)),
            'execution_order' => (int) ($this->input('execution_order', 0)),
            'id_parent' => (int) ($this->input('id_parent', 0)),
            'expected_time' => $this->filled('expected_time') ? (int) $this->input('expected_time') : null,
            'dependency_ids' => array_values(array_filter((array) $this->input('dependency_ids', []))),
        ]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:40'],
            'description' => ['nullable', 'string'],
            'comment' => ['nullable', 'string', 'max:150'],
            'priority' => ['required', 'integer', Rule::in(array_keys(config('project-manager.task_priorities', [])))],
            'status' => ['required', 'integer', Rule::in(array_keys(config('project-manager.task_statuses', [])))],
            'execution_order' => ['nullable', 'integer', 'min:0'],
            'id_parent' => ['nullable', 'integer', 'min:0'],
            'expected_time' => ['nullable', 'integer', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'scheduled_for' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
            'blocked_reason' => ['nullable', 'string', 'max:255'],
            'dependency_ids' => ['nullable', 'array'],
            'dependency_ids.*' => ['integer', 'exists:wt_todo,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'título',
            'expected_time' => 'tempo esperado',
            'scheduled_for' => 'data de execução',
            'deadline' => 'deadline',
            'dependency_ids' => 'dependências',
        ];
    }
}
