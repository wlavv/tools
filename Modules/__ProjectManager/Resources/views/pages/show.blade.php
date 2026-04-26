@extends('layouts.app')
@section('content')
@include('project-manager::Includes.css')
<div class="pm-page">
    <div class="pm-card">
        <div class="pm-title-row">
            <div>
                <h2 class="pm-title-row__title">{{ $project->name }}</h2>
                <div class="pm-muted">{{ $project->description }}</div>
            </div>
            <a href="{{ route('project_manager.tasks.create', $project) }}" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact">
                <span class="lsg-action-btn__icon"><i class="fa-solid fa-plus"></i></span>
                <span>{{ __('project-manager::project_manager.new_task') }}</span>
            </a>
        </div>

        <div class="pm-grid pm-grid-2">
            <div class="pm-info-box">
                <p><strong>{{ __('project-manager::project_manager.show_fields.status') }}:</strong> {{ $project->status }}</p>
                <p><strong>{{ __('project-manager::project_manager.show_fields.website') }}:</strong> {{ $project->website }}</p>
                <p><strong>{{ __('project-manager::project_manager.show_fields.repository') }}:</strong> {{ $project->repository_url }}</p>
                <p><strong>{{ __('project-manager::project_manager.show_fields.documentation') }}:</strong> {{ $project->documentation_url }}</p>
            </div>
            <div class="pm-info-box">
                <p><strong>{{ __('project-manager::project_manager.show_fields.owner') }}:</strong> {{ $project->owner_name }}</p>
                <p><strong>{{ __('project-manager::project_manager.show_fields.owner_email') }}:</strong> {{ $project->owner_email }}</p>
                <p><strong>{{ __('project-manager::project_manager.show_fields.contact') }}:</strong> {{ $project->contact_name }}</p>
                <p><strong>{{ __('project-manager::project_manager.show_fields.phone') }}:</strong> {{ $project->contact_phone }}</p>
            </div>
        </div>
    </div>

    @include('project-manager::Includes._components.task-table', [
        'project' => $project,
        'tasks' => $tasks,
        'taskStatuses' => $taskStatuses,
        'taskPriorities' => $taskPriorities,
    ])
</div>
@endsection
