@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::Includes.css')

<div class="pm-page">
    @include('project-manager::Includes._components.header', ['title' => 'Editar Projeto', 'subtitle' => $project->name])
    <div class="pm-card">
        <form method="POST" action="{{ route('project_manager.update', $project) }}">
            @csrf
            @method('PUT')
            @include('project-manager::Includes._components.form', ['project' => $project, 'statuses' => $statuses, 'projects' => $projects])
        </form>
    </div>
</div>
@endsection
