@extends('layouts.app')
@section('content')
@include('project-manager::Includes.css')
@include('project-manager::Includes.js')
<div class="pm-page">
    @include('project-manager::Includes._components.header', ['title' => 'Editar Projeto', 'subtitle' => $project->name])
    <div class="pm-card">
        <form method="POST" action="{{ route('project_manager.update', $project) }}">
            @csrf
            @method('PUT')
            @include('project-manager::Includes._components.form-tabs', ['project' => $project, 'statuses' => $statuses, 'projects' => $projects])
        </form>
    </div>
</div>
@endsection
