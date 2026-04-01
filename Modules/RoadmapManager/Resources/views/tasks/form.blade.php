@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<h1 class="h3 mb-3">{{ $task->exists ? 'Edit Task' : 'New Task' }}</h1>
<form method="POST" action="{{ $task->exists ? route('roadmap.tasks.update', $task->id) : route('roadmap.tasks.store') }}">
    @csrf
    @if($task->exists) @method('PUT') @endif
    <div class="card shadow-sm">
        <div class="card-body row g-3">
            <div class="col-md-6">
                <label class="form-label">Project</label>
                <select name="project_id" class="form-select" required>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id', $task->project_id) == $project->id)>{{ $project->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Milestone</label>
                <select name="milestone_id" class="form-select">
                    <option value="">-- none --</option>
                    @foreach($milestones as $milestone)
                        <option value="{{ $milestone->id }}" @selected(old('milestone_id', $task->milestone_id) == $milestone->id)>{{ $milestone->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Parent Task</label>
                <select name="parent_id" class="form-select">
                    <option value="">-- none --</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" @selected(old('parent_id', $task->parent_id) == $parent->id)>{{ $parent->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Level</label>
                <input type="number" name="level" class="form-control" value="{{ old('level', $task->level ?: 1) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Code</label>
                <input name="code" class="form-control" value="{{ old('code', $task->code) }}">
            </div>
            <div class="col-md-8">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" value="{{ old('title', $task->title) }}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    @foreach(['low','medium','high','critical'] as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', $task->priority ?: 'medium') === $priority)>{{ $priority }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    @foreach(['backlog','todo','in_progress','in_review','blocked','completed','cancelled','deferred'] as $status)
                        <option value="{{ $status }}" @selected(old('status', $task->status ?: 'backlog') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Start</label>
                <input type="date" name="planned_start_date" class="form-control" value="{{ old('planned_start_date', optional($task->planned_start_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">End</label>
                <input type="date" name="planned_end_date" class="form-control" value="{{ old('planned_end_date', optional($task->planned_end_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Deadline</label>
                <input type="date" name="deadline" class="form-control" value="{{ old('deadline', optional($task->deadline)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Estimated Hours</label>
                <input type="number" step="0.25" name="estimated_hours" class="form-control" value="{{ old('estimated_hours', $task->estimated_hours) }}">
            </div>
            <div class="col-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="5">{{ old('description', $task->description) }}</textarea>
            </div>
        </div>
        <div class="card-footer">
            <a href="{{ route('roadmap.tasks.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-angle-left"></i> Back</a>
            <button class="btn btn-outline-primary"><i class="fa-solid fa-floppy-disk"></i> Save</button>
        </div>
    </div>
</form>
@endsection
