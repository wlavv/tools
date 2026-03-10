@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::Includes.css')

<div class="pm-page">
    @include('project-manager::Includes._components.header', ['title' => 'Novo Projeto', 'subtitle' => 'Criar novo projeto'])
    <div class="pm-card">
        <form method="POST" action="{{ route('project_manager.store') }}">
            @csrf
            @include('project-manager::Includes._components.form', ['project' => null, 'statuses' => $statuses, 'projects' => $projects])
        </form>
    </div>
</div>
@endsection
