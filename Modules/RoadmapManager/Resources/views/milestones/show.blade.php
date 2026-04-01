@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<h1 class="h3 mb-3">{{ $milestone->name }}</h1>
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <p><strong>Project:</strong> {{ $milestone->project->name ?? '-' }}</p>
        <p><strong>Status:</strong> {{ $milestone->status }}</p>
        <p><strong>End:</strong> {{ optional($milestone->planned_end_date)->format('Y-m-d') }}</p>
        <p class="mb-0"><strong>Description:</strong> {{ $milestone->description }}</p>
    </div>
</div>
<div class="card shadow-sm">
    <div class="card-header">Tasks</div>
    <ul class="list-group list-group-flush">
        @forelse($milestone->tasks as $task)
            <li class="list-group-item"><a href="{{ route('roadmap.tasks.show', $task->id) }}">{{ $task->title }}</a></li>
        @empty
            <li class="list-group-item text-muted">No tasks found.</li>
        @endforelse
    </ul>
</div>
@endsection
