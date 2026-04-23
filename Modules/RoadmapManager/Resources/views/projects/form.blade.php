@extends('layouts.app')

@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<form method="POST" action="{{ $project->exists ? route('roadmap_manager.projects.update', $project->id) : route('roadmap_manager.projects.store') }}">@csrf @if($project->exists) @method('PUT') @endif
<div class="rm-form-card"><div class="rm-form-grid">
<div><label class="rm-label">Name</label><input name="name" class="rm-input" value="{{ old('name', $project->name) }}" required></div>
<div><label class="rm-label">Status</label><input name="status" class="rm-input" value="{{ old('status', $project->status ?: '1') }}"></div>
<div><label class="rm-label">Priority</label><input type="number" name="priority" class="rm-input" value="{{ old('priority', $project->priority) }}"></div>
<div><label class="rm-label">Slug</label><input name="slug" class="rm-input" value="{{ old('slug', $project->slug) }}"></div>
<div><label class="rm-label">Website</label><input name="website" class="rm-input" value="{{ old('website', $project->website) }}"></div>
<div><label class="rm-label">Start Date</label><input type="date" name="start_date" class="rm-input" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}"></div>
<div><label class="rm-label">Deadline</label><input type="date" name="deadline" class="rm-input" value="{{ old('deadline', optional($project->deadline)->format('Y-m-d')) }}"></div>
<div class="rm-form-grid__full"><label class="rm-label">Groups</label><select name="group_ids[]" class="rm-select" multiple size="5">@foreach($groups as $group)<option value="{{ $group->id }}" @selected(in_array($group->id, old('group_ids', $selectedGroups ?? [])))>{{ $group->name }}</option>@endforeach</select></div>
<div class="rm-form-grid__full"><label class="rm-label">Description</label><textarea name="description" class="rm-textarea" rows="4">{{ old('description', $project->description) }}</textarea></div>
@endsection
