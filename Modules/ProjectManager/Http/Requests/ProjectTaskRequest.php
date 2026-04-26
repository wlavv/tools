<?php

namespace Modules\ProjectManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'id_parent' => $this->filled('id_parent') ? (int) $this->input('id_parent') : 0,
            'priority' => $this->filled('priority') ? (int) $this->input('priority') : 2,
            'status' => $this->filled('status') ? (int) $this->input('status') : 0,
            'expected_time' => $this->filled('expected_time') ? (int) $this->input('expected_time') : null,
            'execution_order' => $this->filled('execution_order') ? (int) $this->input('execution_order') : 0,
        ]);
    }

    public function rules(): array
    {
        $project = $this->route('project');
        $projectId = is_object($project) ? (int) $project->id : (int) $project;

        return [
            'id_project' => ['nullable', 'integer'],
            'id_parent' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($projectId) {
                    if ((int) $value === 0) {
                        return;
                    }

                    $exists = \Modules\ProjectManager\Models\ProjectTask::where('id_project', $projectId)
                        ->where('id', (int) $value)
                        ->exists();

                    if (!$exists) {
                        $fail('A tarefa pai selecionada não é válida.');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'comment' => ['nullable', 'string', 'max:150'],
            'priority' => ['required', 'integer', 'between:1,4'],
            'status' => ['required', 'integer', 'between:0,5'],
            'type' => ['nullable', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'deadline' => ['nullable', 'date'],
            'scheduled_for' => ['nullable', 'date'],
            'expected_time' => ['nullable', 'integer', 'min:0'],
            'execution_order' => ['nullable', 'integer', 'min:0'],
            'dependencies' => ['nullable', 'array'],
            'dependencies.*' => [
                'integer',
                Rule::exists('wt_todo', 'id')->where(fn ($query) => $query->where('id_project', $projectId)),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => __('project-manager::tasks.title'),
            'id_parent' => __('project-manager::tasks.parent_task'),
            'priority' => __('project-manager::tasks.priority'),
            'status' => __('project-manager::tasks.status'),
            'start_date' => __('project-manager::tasks.start_date'),
            'deadline' => __('project-manager::tasks.deadline'),
            'scheduled_for' => __('project-manager::tasks.scheduled_for'),
            'expected_time' => __('project-manager::tasks.expected_time'),
            'execution_order' => __('project-manager::tasks.execution_order'),
            'dependencies' => __('project-manager::tasks.dependencies'),
        ];
    }
}
