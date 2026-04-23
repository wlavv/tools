@extends('layouts.app')

@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<div class="rm-stack">
<div class="rm-panel"><div class="rm-meta-grid"><div class="rm-meta"><div class="rm-meta__label">Status</div><div class="rm-meta__value">{{ $project->status }}</div></div><div class="rm-meta"><div class="rm-meta__label">Priority</div><div class="rm-meta__value">{{ $project->priority ?: '-' }}</div></div><div class="rm-meta"><div class="rm-meta__label">Website</div><div class="rm-meta__value">{{ $project->website ?: '-' }}</div></div><div class="rm-meta"><div class="rm-meta__label">Deadline</div><div class="rm-meta__value">{{ $project->deadline ?: '-' }}</div></div><div class="rm-meta rm-form-grid__full"><div class="rm-meta__label">Description</div><div class="rm-meta__value">{{ $project->description ?: '-' }}</div></div></div></div>
<div class="row g-3"><div class="col-lg-6"><div class="rm-panel"><div class="rm-title-row" style="margin-bottom:.75rem"><h3 class="rm-title-row__title">Groups</h3></div><ul class="rm-list">@forelse($project->roadmapGroups as $group)<li>{{ $group->name }}</li>@empty<li class="rm-muted">No groups linked.</li>@endforelse</ul></div></div><div class="col-lg-6"><div class="rm-panel"><div class="rm-title-row" style="margin-bottom:.75rem"><h3 class="rm-title-row__title">Milestones</h3></div><ul class="rm-list">@forelse($project->milestones as $milestone)<li><a href="{{ route('roadmap_manager.milestones.show', $milestone->id) }}">{{ $milestone->name }}</a></li>@empty<li class="rm-muted">No milestones found.</li>@endforelse</ul></div></div></div>
<div class="rm-panel"><div class="rm-title-row" style="margin-bottom:.75rem"><h3 class="rm-title-row__title">Tasks</h3></div><ul class="rm-list">@forelse($project->tasks as $task)<li><a href="{{ route('roadmap_manager.tasks.show', $task->id) }}">{{ $task->title }}</a></li>@empty<li class="rm-muted">No tasks found.</li>@endforelse</ul></div>
</div>
@endsection
