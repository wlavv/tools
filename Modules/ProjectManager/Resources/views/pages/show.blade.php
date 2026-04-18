@extends('layouts.app')
@section('content')
@include('project-manager::Includes.css')
<div class="pm-page">
    <div class="pm-card">
        <h2>{{ $project->name }}</h2>
        <p>{{ $project->description }}</p>
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
</div>
@endsection
