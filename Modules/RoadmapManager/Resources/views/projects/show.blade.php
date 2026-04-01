@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $project->name }}</h1>
    <div class="btn-group">
        <a href="{{ route('roadmap.projects.edit', $project->id) }}" class="btn btn-outline-warning"><i class="fa-solid fa-pencil"></i> Edit</a>
        <a href="{{ route('roadmap.milestones.create') }}" class="btn btn-outline-success"><i class="fa-solid fa-plus"></i> Milestone</a>
    </div>
</div>
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <p><strong>Status:</strong> {{ $project->status }}</p>
                <p><strong>Deadline:</strong> {{ $project->deadline }}</p>
                <p><strong>Groups:</strong>
                    @foreach($project->roadmapGroups as $group)
                        <span class="badge" style="background:{{ $group->color }}">{{ $group->name }}</span>
                    @endforeach
                </p>
                <p class="mb-0"><strong>Description:</strong> {{ $project->description }}</p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header">Milestones</div>
            <ul class="list-group list-group-flush">
                @forelse($project->milestones as $milestone)
                    <li class="list-group-item"><a href="{{ route('roadmap.milestones.show', $milestone->id) }}">{{ $milestone->name }}</a></li>
                @empty
                    <li class="list-group-item text-muted">No milestones found.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
