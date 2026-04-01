@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $group->name }}</h1>
    <a href="{{ route('roadmap.groups.edit', $group->id) }}" class="btn btn-outline-warning"><i class="fa-solid fa-pencil"></i> Edit</a>
</div>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <p><strong>Slug:</strong> {{ $group->slug }}</p>
        <p><strong>Status:</strong> {{ $group->status }}</p>
        <p class="mb-0"><strong>Description:</strong> {{ $group->description }}</p>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-header">Linked Projects</div>
    <ul class="list-group list-group-flush">
        @forelse($group->projects as $project)
            <li class="list-group-item"><a href="{{ route('roadmap.projects.show', $project->id) }}">{{ $project->name }}</a></li>
        @empty
            <li class="list-group-item text-muted">No linked projects.</li>
        @endforelse
    </ul>
</div>
@endsection
