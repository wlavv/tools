@extends('layouts.app')
@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<div class="rm-board">@foreach($columns as $status => $tasks)<div class="rm-kanban-column"><div class="rm-kanban-column__header">{{ str_replace('_',' ', $status) }}</div><div class="rm-kanban-column__body">@forelse($tasks as $task)<div class="rm-kanban-item"><div class="fw-bold small"><a href="{{ route('roadmap_manager.tasks.show', $task->id) }}">{{ $task->title }}</a></div><div class="rm-muted small">{{ $task->project->name ?? '-' }}</div></div>@empty<div class="rm-muted small">No tasks.</div>@endforelse</div></div>@endforeach</div>
@endsection
