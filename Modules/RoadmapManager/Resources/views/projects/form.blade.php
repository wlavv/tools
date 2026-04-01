@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<h1 class="h3 mb-3">{{ $project->exists ? 'Edit Project' : 'New Project' }}</h1>
<form method="POST" action="{{ $project->exists ? route('roadmap.projects.update', $project->id) : route('roadmap.projects.store') }}">
    @csrf
    @if($project->exists) @method('PUT') @endif
    <div class="card shadow-sm">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="{{ old('name', $project->name) }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <input name="status" class="form-control" value="{{ old('status', $project->status ?: '1') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Priority</label>
                <input type="number" name="priority" class="form-control" value="{{ old('priority', $project->priority) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Slug</label>
                <input name="slug" class="form-control" value="{{ old('slug', $project->slug) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Website</label>
                <input name="website" class="form-control" value="{{ old('website', $project->website) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="{{ old('start_date', optional($project->start_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Deadline</label>
                <input type="date" name="deadline" class="form-control" value="{{ old('deadline', optional($project->deadline)->format('Y-m-d')) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Groups</label>
                <select name="group_ids[]" class="form-select" multiple size="5">
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" @selected(in_array($group->id, old('group_ids', $selectedGroups ?? [])))>{{ $group->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $project->description) }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('roadmap.projects.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i> Back</a>
            <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        </div>
    </div>
</form>
@endsection
