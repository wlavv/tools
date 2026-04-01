@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<h1 class="h3 mb-3">{{ $milestone->exists ? 'Edit Milestone' : 'New Milestone' }}</h1>
<form method="POST" action="{{ $milestone->exists ? route('roadmap.milestones.update', $milestone->id) : route('roadmap.milestones.store') }}">
    @csrf
    @if($milestone->exists) @method('PUT') @endif
    <div class="card shadow-sm">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Project</label>
                <select name="project_id" class="form-select" required>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id', $milestone->project_id) == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input name="name" class="form-control" value="{{ old('name', $milestone->name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['planned','in_progress','completed','delayed','cancelled'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $milestone->status ?: 'planned') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Start</label>
                <input type="date" name="planned_start_date" class="form-control" value="{{ old('planned_start_date', optional($milestone->planned_start_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">End</label>
                <input type="date" name="planned_end_date" class="form-control" value="{{ old('planned_end_date', optional($milestone->planned_end_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $milestone->description) }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('roadmap.milestones.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i> Back</a>
            <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        </div>
    </div>
</form>
@endsection
