@extends('layouts.app')

@section('content')
@include('project-manager::Includes.css')
<div class="container-fluid py-3 pm-page">
    @includeIf('project-manager::partials.alerts')

    <form method="POST" action="{{ $action }}" class="pm-card pm-form-card">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <input type="hidden" name="id_project" value="{{ $project->id }}">

        <div class="pm-form-grid">
            <div>
                <label class="pm-form-label">{{ __('project-manager::tasks.title') }}</label>
                <input type="text" name="title" value="{{ old('title', $task->title) }}" class="pm-form-control @error('title') is-invalid @enderror" required>
                @error('title')<div class="pm-field-error">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="pm-form-label">{{ __('project-manager::tasks.parent_task') }}</label>
                <select name="id_parent" class="pm-form-control @error('id_parent') is-invalid @enderror">
                    <option value="0" {{ (int) old('id_parent', $parentTask?->id ?? $task->id_parent ?? 0) === 0 ? 'selected' : '' }}>— Task principal —</option>
                    @foreach($availableTasks as $availableTask)
                        <option value="{{ $availableTask->id }}"
                            {{ (int) old('id_parent', $parentTask?->id ?? $task->id_parent ?? 0) === (int) $availableTask->id ? 'selected' : '' }}>
                            #{{ $availableTask->id }} — {{ $availableTask->title }}
                        </option>
                    @endforeach
                </select>
                @error('id_parent')<div class="pm-field-error">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="pm-form-label">{{ __('project-manager::tasks.priority') }}</label>
                <select name="priority" class="pm-form-control">
                    @foreach(\Modules\ProjectManager\Models\ProjectTask::priorityOptions() as $value => $label)
                        <option value="{{ $value }}" @selected((int) old('priority', $task->priority) === (int) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="pm-form-label">{{ __('project-manager::tasks.status') }}</label>
                <select name="status" class="pm-form-control">
                    @foreach(\Modules\ProjectManager\Models\ProjectTask::statusOptions() as $value => $label)
                        <option value="{{ $value }}" @selected((int) old('status', $task->status) === (int) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="pm-form-label">{{ __('project-manager::tasks.type') }}</label>
                <input type="text" name="type" value="{{ old('type', $task->type) }}" class="pm-form-control">
            </div>

            <div>
                <label class="pm-form-label">{{ __('project-manager::tasks.expected_time') }}</label>
                <input type="number" name="expected_time" value="{{ old('expected_time', $task->expected_time) }}" class="pm-form-control" min="0">
            </div>

            <div>
                <label class="pm-form-label">{{ __('project-manager::tasks.start_date') }}</label>
                <input type="datetime-local" name="start_date" value="{{ old('start_date', optional($task->start_date)->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i')) }}" class="pm-form-control" required>
            </div>

            <div>
                <label class="pm-form-label">{{ __('project-manager::tasks.deadline') }}</label>
                <input type="datetime-local" name="deadline" value="{{ old('deadline', optional($task->deadline)->format('Y-m-d\TH:i')) }}" class="pm-form-control">
            </div>

            <div>
                <label class="pm-form-label">{{ __('project-manager::tasks.scheduled_for') }}</label>
                <input type="datetime-local" name="scheduled_for" value="{{ old('scheduled_for', optional($task->scheduled_for)->format('Y-m-d\TH:i')) }}" class="pm-form-control">
            </div>

            <div>
                <label class="pm-form-label">{{ __('project-manager::tasks.execution_order') }}</label>
                <input type="number" name="execution_order" value="{{ old('execution_order', $task->execution_order ?? 0) }}" class="pm-form-control" min="0">
            </div>

            <div class="pm-form-grid-1">
                <label class="pm-form-label">{{ __('project-manager::tasks.dependencies') }}</label>
                <select name="dependencies[]" class="pm-form-control" multiple size="6">
                    @foreach($availableTasks as $availableTask)
                        <option value="{{ $availableTask->id }}"
                            @selected(in_array($availableTask->id, old('dependencies', $task->dependency_ids ?? [])))>
                            #{{ $availableTask->id }} — {{ $availableTask->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="pm-form-grid-1">
                <label class="pm-form-label">{{ __('project-manager::tasks.description') }}</label>
                <textarea name="description" class="pm-form-control pm-form-textarea">{{ old('description', $task->description) }}</textarea>
            </div>

            <div class="pm-form-grid-1">
                <label class="pm-form-label">{{ __('project-manager::tasks.comment') }}</label>
                <input type="text" name="comment" value="{{ old('comment', $task->comment) }}" class="pm-form-control">
            </div>
        </div>

        <div class="pm-form-actions mt-3">
            <button type="submit" class="lsg-action-btn lsg-action-btn--primary">
                <span class="lsg-action-btn__icon"><i class="fa-solid fa-floppy-disk"></i></span>
                <span>{{ __('project-manager::tasks.save') }}</span>
            </button>

            <a href="{{ route('project_manager.show', $project) }}" class="lsg-action-btn lsg-action-btn--primary">
                <span class="lsg-action-btn__icon"><i class="fa-solid fa-angle-left"></i></span>
                <span>{{ __('project-manager::tasks.back') }}</span>
            </a>
        </div>
    </form>
</div>
@endsection
