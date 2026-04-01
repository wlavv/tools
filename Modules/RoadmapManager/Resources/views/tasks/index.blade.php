@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Tasks</h1>
    <div class="btn-group">
        <a href="{{ route('roadmap.tasks.tree') }}" class="btn btn-outline-primary"><i class="fa-solid fa-sitemap"></i> Tree</a>
        <a href="{{ route('roadmap.tasks.gantt') }}" class="btn btn-outline-primary"><i class="fa-solid fa-chart-gantt"></i> Gantt</a>
        <a href="{{ route('roadmap.tasks.kanban') }}" class="btn btn-outline-primary"><i class="fa-solid fa-table-columns"></i> Kanban</a>
        <a href="{{ route('roadmap.tasks.create') }}" class="btn btn-outline-success"><i class="fa-solid fa-plus"></i> New</a>
    </div>
</div>
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead><tr><th>Title</th><th>Project</th><th>Status</th><th>Priority</th><th>Deadline</th><th></th></tr></thead>
            <tbody>
            @forelse($tasks as $task)
                <tr>
                    <td>{{ $task->title }}</td>
                    <td>{{ $task->project->name ?? '-' }}</td>
                    <td>{{ $task->status }}</td>
                    <td>{{ $task->priority }}</td>
                    <td>{{ optional($task->deadline)->format('Y-m-d') }}</td>
                    <td class="text-end">
                        <a href="{{ route('roadmap.tasks.show', $task->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></a>
                        <a href="{{ route('roadmap.tasks.edit', $task->id) }}" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-muted">No tasks found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $tasks->links() }}</div>
@endsection
