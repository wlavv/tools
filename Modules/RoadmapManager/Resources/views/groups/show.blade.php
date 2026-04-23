@extends('layouts.app')

@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<div class="rm-title-row"><h2 class="rm-title-row__title">{{ $group->name }}</h2><a href="{{ route('roadmap_manager.groups.edit', $group->id) }}" class="lsg-action-btn lsg-action-btn--warning"><i class="fa-solid fa-pencil"></i><span>Edit</span></a></div>
<div class="rm-stack">
    <div class="rm-panel">
        <div class="rm-meta-grid">
            <div class="rm-meta"><div class="rm-meta__label">Slug</div><div class="rm-meta__value">{{ $group->slug ?: '-' }}</div></div>
            <div class="rm-meta"><div class="rm-meta__label">Status</div><div class="rm-meta__value">{{ $group->status }}</div></div>
            <div class="rm-meta rm-form-grid__full"><div class="rm-meta__label">Description</div><div class="rm-meta__value">{{ $group->description ?: '-' }}</div></div>
        </div>
    </div>
    <div class="rm-panel">
        <div class="rm-title-row" style="margin-bottom:.75rem"><h3 class="rm-title-row__title">Linked Projects</h3></div>
        <ul class="rm-list">@forelse($group->projects as $project)<li><a href="{{ route('roadmap_manager.projects.show', $project->id) }}">{{ $project->name }}</a></li>@empty<li class="rm-muted">No linked projects.</li>@endforelse</ul>
    </div>
</div>
@endsection
