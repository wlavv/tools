@extends('layouts.app')
@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<div class="rm-panel"><div class="rm-table-wrap"><table class="rm-table"><thead><tr><th>Title</th><th>Project</th><th>Status</th><th>Priority</th><th>Deadline</th><th class="text-end">Actions</th></tr></thead><tbody>@forelse($tasks as $task)<tr><td>{{ $task->title }}</td><td>{{ $task->project->name ?? '-' }}</td><td>{{ $task->status }}</td><td>{{ $task->priority }}</td><td>{{ optional($task->deadline)->format('Y-m-d') }}</td><td class="text-end"><div class="rm-table-actions"><a href="{{ route('roadmap_manager.tasks.show', $task->id) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-eye"></i></a><a href="{{ route('roadmap_manager.tasks.edit', $task->id) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact"><i class="fa-solid fa-pencil"></i></a></div></td></tr>@empty<tr><td colspan="6" class="rm-muted">No tasks found.</td></tr>@endforelse</tbody></table></div></div><div class="mt-3">{{ $tasks->links() }}</div>
@endsection
