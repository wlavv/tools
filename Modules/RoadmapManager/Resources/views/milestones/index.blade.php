@extends('layouts.app')
@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<div class="rm-title-row"><h2 class="rm-title-row__title">Milestones</h2><a href="{{ route('roadmap_manager.milestones.create') }}" class="lsg-action-btn lsg-action-btn--success"><i class="fa-solid fa-plus"></i><span>New</span></a></div>
<div class="rm-panel"><div class="rm-table-wrap"><table class="rm-table"><thead><tr><th>Name</th><th>Project</th><th>Status</th><th>End</th><th class="text-end">Actions</th></tr></thead><tbody>@forelse($milestones as $milestone)<tr><td>{{ $milestone->name }}</td><td>{{ $milestone->project->name ?? '-' }}</td><td>{{ $milestone->status }}</td><td>{{ optional($milestone->planned_end_date)->format('Y-m-d') }}</td><td class="text-end"><div class="rm-table-actions"><a href="{{ route('roadmap_manager.milestones.show', $milestone->id) }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact"><i class="fa-solid fa-eye"></i></a><a href="{{ route('roadmap_manager.milestones.edit', $milestone->id) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact"><i class="fa-solid fa-pencil"></i></a></div></td></tr>@empty<tr><td colspan="5" class="rm-muted">No milestones found.</td></tr>@endforelse</tbody></table></div></div><div class="mt-3">{{ $milestones->links() }}</div>
@endsection
