@extends('layouts.app')
@section('content')
@include('project-manager::Includes.css')
<div class="pm-page">
    @if($errors->any())
        <div class="pm-alert pm-alert--warning">
            <strong>There are validation errors in the form.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="lsg-form" method="POST" action="{{ $action }}" class="pm-card" novalidate>
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="pm-title-row">
            <div>
                <h2 class="pm-title-row__title">{{ $task ? __('project-manager::project_manager.edit_task') : __('project-manager::project_manager.new_task') }}</h2>
                <div class="pm-muted">{{ $project->name }}</div>
            </div>
        </div>

        <div class="pm-form-grid">
            <div class="pm-form-grid__full">
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.title') }}</label>
                <input type="text" name="title" class="pm-input @error('title') is-invalid @enderror" value="{{ old('title', $task->title ?? '') }}" required>
                @error('title')<div class="pm-field-error">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.type') }}</label>
                <select name="type" class="pm-select">
                    <option value="">—</option>
                    @foreach($taskTypes as $key => $label)
                        <option value="{{ $key }}" @selected(old('type', $task->type ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.status') }}</label>
                <select name="status" class="pm-select">
                    @foreach($taskStatuses as $key => $label)
                        <option value="{{ $key }}" @selected((string) old('status', $task->status ?? 0) === (string) $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.priority') }}</label>
                <select name="priority" class="pm-select">
                    @foreach($taskPriorities as $key => $label)
                        <option value="{{ $key }}" @selected((string) old('priority', $task->priority ?? 3) === (string) $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.execution_order') }}</label>
                <input type="number" name="execution_order" class="pm-input" value="{{ old('execution_order', $task->execution_order ?? 0) }}" min="0">
            </div>

            <div>
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.parent_task') }}</label>
                <select name="id_parent" class="pm-select">
                    <option value="0">—</option>
                    @foreach($parentTasks as $parentTask)
                        <option value="{{ $parentTask->id }}" @selected((int) old('id_parent', $task->id_parent ?? 0) === (int) $parentTask->id)>{{ $parentTask->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.expected_time') }}</label>
                <input type="number" name="expected_time" class="pm-input" value="{{ old('expected_time', $task->expected_time ?? '') }}" min="0">
            </div>

            <div>
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.start_date') }}</label>
                <input type="datetime-local" name="start_date" class="pm-input" value="{{ old('start_date', optional($task?->start_date)->format('Y-m-d\\TH:i')) }}">
            </div>

            <div>
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.scheduled_for') }}</label>
                <input type="datetime-local" name="scheduled_for" class="pm-input" value="{{ old('scheduled_for', optional($task?->scheduled_for)->format('Y-m-d\\TH:i')) }}">
            </div>

            <div>
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.deadline') }}</label>
                <input type="datetime-local" name="deadline" class="pm-input" value="{{ old('deadline', optional($task?->deadline)->format('Y-m-d\\TH:i')) }}">
            </div>

            <div class="pm-form-grid__full">
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.dependencies') }}</label>
                <select name="dependency_ids[]" class="pm-select" multiple size="6">
                    @foreach($dependencyOptions as $dependency)
                        <option value="{{ $dependency->id }}" @selected(in_array((int) $dependency->id, array_map('intval', old('dependency_ids', $selectedDependencies ?? [])), true))>{{ $dependency->title }}</option>
                    @endforeach
                </select>
                <div class="pm-muted mt-1">Use Ctrl/Cmd to select multiple dependencies.</div>
            </div>

            <div class="pm-form-grid__full">
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.comment') }}</label>
                <input type="text" name="comment" class="pm-input" maxlength="150" value="{{ old('comment', $task->comment ?? '') }}">
            </div>

            <div class="pm-form-grid__full">
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.description') }}</label>
                <textarea name="description" class="pm-textarea">{{ old('description', $task->description ?? '') }}</textarea>
            </div>

            <div class="pm-form-grid__full">
                <label class="pm-label">{{ __('project-manager::project_manager.task_fields.blocked_reason') }}</label>
                <input type="text" name="blocked_reason" class="pm-input" maxlength="255" value="{{ old('blocked_reason', $task->blocked_reason ?? '') }}">
            </div>
        </div>
    </form>
</div>
@endsection
