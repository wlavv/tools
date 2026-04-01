@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $task->title }}</h1>
    <a href="{{ route('roadmap.tasks.edit', $task->id) }}" class="btn btn-outline-warning"><i class="fa-solid fa-pencil"></i> Edit</a>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <p><strong>Project:</strong> {{ $task->project->name ?? '-' }}</p>
                <p><strong>Milestone:</strong> {{ $task->milestone->name ?? '-' }}</p>
                <p><strong>Status:</strong> {{ $task->status }}</p>
                <p><strong>Priority:</strong> {{ $task->priority }}</p>
                <p class="mb-0"><strong>Description:</strong> {{ $task->description }}</p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header">Comments</div>
            <div class="card-body">
                <form method="POST" action="{{ route('roadmap.tasks.comments.store', $task->id) }}" class="mb-3">
                    @csrf
                    <textarea name="content" class="form-control mb-2" rows="3" placeholder="Add comment"></textarea>
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                </form>
                @forelse($task->comments as $comment)
                    <div class="border rounded p-2 mb-2">{{ $comment->content }}</div>
                @empty
                    <div class="text-muted">No comments yet.</div>
                @endforelse
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">Time Logs</div>
            <div class="card-body">
                <form method="POST" action="{{ route('roadmap.tasks.time_logs.store', $task->id) }}" class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-3"><input type="number" step="0.25" name="logged_hours" class="form-control" placeholder="Hours"></div>
                    <div class="col-md-3"><input type="date" name="log_date" class="form-control"></div>
                    <div class="col-md-4"><input name="description" class="form-control" placeholder="Description"></div>
                    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Save</button></div>
                </form>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Date</th><th>Hours</th><th>Description</th></tr></thead>
                        <tbody>
                        @forelse($task->timeLogs as $log)
                            <tr><td>{{ optional($log->log_date)->format('Y-m-d') }}</td><td>{{ $log->logged_hours }}</td><td>{{ $log->description }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-muted">No time logs yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header">Attachments</div>
            <div class="card-body">
                <form method="POST" action="{{ route('roadmap.tasks.attachments.store', $task->id) }}" enctype="multipart/form-data" class="mb-3">
                    @csrf
                    <input type="file" name="file" class="form-control mb-2">
                    <button class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-floppy-disk"></i> Upload</button>
                </form>
                <ul class="list-group">
                    @forelse($task->attachments as $attachment)
                        <li class="list-group-item">{{ $attachment->filename }}</li>
                    @empty
                        <li class="list-group-item text-muted">No attachments yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">Children & Dependencies</div>
            <div class="card-body">
                <p><strong>Children:</strong> {{ $task->children->count() }}</p>
                <ul>
                    @foreach($task->children as $child)
                        <li><a href="{{ route('roadmap.tasks.show', $child->id) }}">{{ $child->title }}</a></li>
                    @endforeach
                </ul>
                <p><strong>Depends on:</strong></p>
                <ul>
                    @forelse($task->dependencies as $dependency)
                        <li><a href="{{ route('roadmap.tasks.show', $dependency->id) }}">{{ $dependency->title }}</a></li>
                    @empty
                        <li class="text-muted">No dependencies.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
