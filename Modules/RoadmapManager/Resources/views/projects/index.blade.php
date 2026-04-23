@extends('layouts.app')

@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<div class="rm-title-row">
<div class="rm-panel"><div class="rm-table-wrap"><table class="rm-table"><thead><tr><th>Name</th><th>Status</th><th>Priority</th><th>Groups</th><th>Deadline</th><th class="text-end">Actions</th></tr></thead><tbody>@forelse($projects as $project)<tr><td>{{ $project->name }}</td><td>{{ $project->status }}</td><td>{{ $project->priority }}</td><td>@foreach($project->roadmapGroups as $group)<span class="badge" style="background:{{ $group->color }}">{{ $group->name }}</span>@endforeach</td><td>{{ $project->deadline }}</td><td class="text-end"><div class="rm-table-actions"><a href="{{ route('roadmap_manager.projects.show', $project->id) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-eye"></i></a><a href="{{ route('roadmap_manager.projects.edit', $project->id) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact"><i class="fa-solid fa-pencil"></i></a></div></td></tr>@empty<tr><td colspan="6" class="rm-muted">No projects found.</td></tr>@endforelse</tbody></table></div></div><div class="mt-3">{{ $projects->links() }}</div>
@endsection
