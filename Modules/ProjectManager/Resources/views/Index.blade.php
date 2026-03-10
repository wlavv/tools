@extends(config('project-manager.layout', 'layouts.app'))

@section('content')
@include('project-manager::Includes.css')
@include('project-manager::Includes.js')

<div class="pm-page">
    @include('project-manager::Includes._components.header', ['title' => 'Project Manager', 'subtitle' => 'Gestão centralizada de projetos'])
    @include('project-manager::Includes._components.stats', ['stats' => $stats])
    @include('project-manager::Includes._components.filters')
    @include('project-manager::Includes._components.cards', ['projects' => $projects])
    @include('project-manager::Includes._components.table', ['projects' => $projects])
</div>
@endsection
