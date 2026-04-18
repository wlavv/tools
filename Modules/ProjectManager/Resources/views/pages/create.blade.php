@extends('layouts.app')
@section('content')
@include('project-manager::Includes.css')
@include('project-manager::Includes.js')
<div class="pm-page">
    @include('project-manager::Includes._components.header', ['title' => __('project-manager::project_manager.new_project'), 'subtitle' => __('project-manager::project_manager.create_project_subtitle')])
    <div class="pm-card">
        <form method="POST" action="{{ route('project_manager.store') }}">
            @csrf
            @include('project-manager::Includes._components.form-tabs', ['project' => null, 'statuses' => $statuses, 'projects' => $projects])
        </form>
    </div>
</div>
@endsection
