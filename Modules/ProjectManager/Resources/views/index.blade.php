@extends('layouts.app')
@section('content')
    @include('project-manager::Includes.css')
    @include('project-manager::Includes.js')
    <div class="pm-page">
        @include('project-manager::Includes._components.stats', ['stats' => $stats])
        <div class="pm-groups">
            @forelse($groups as $group)
                @include('project-manager::Includes._components.group-block', ['group' => $group])
            @empty
                <div class="pm-empty">Sem grupos de projetos.</div>
            @endforelse
        </div>
    </div>
@endsection
