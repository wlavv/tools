@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<h1 class="h3 mb-3">Kanban Board</h1>
<div class="row g-3">
    @foreach($columns as $status => $tasks)
        <div class="col-lg-2 col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header text-capitalize">{{ str_replace('_',' ', $status) }}</div>
                <div class="card-body p-2">
                    @forelse($tasks as $task)
                        <div class="border rounded p-2 mb-2">
                            <div class="fw-bold small"><a href="{{ route('roadmap.tasks.show', $task->id) }}">{{ $task->title }}</a></div>
                            <div class="text-muted small">{{ $task->project->name ?? '-' }}</div>
                        </div>
                    @empty
                        <div class="text-muted small">No tasks.</div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection
