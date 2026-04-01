@extends('layouts.app')

@section('content')
<div>
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div class="d-flex flex-wrap gap-2">
            @foreach(($actions ?? []) as $action)
                <a href="{{ $action['url'] }}" class="btn btn-{{ $action['class'] ?? 'outline-primary' }}">
                    <i class="{{ $action['icon'] ?? 'fa-solid fa-circle' }}"></i>
                    {{ $action['name'] }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <strong>Top Tasks</strong>
                </div>
                <div class="card-body">
                    @forelse($dashboard['today'] as $task)
                        <div class="d-flex justify-content-between align-items-start border-bottom py-2">
                            <div>
                                <div class="fw-semibold">{{ $task->title }}</div>
                                <div class="small text-muted">{{ $task->project }} • {{ strtoupper($task->priority) }}</div>
                            </div>
                            <span class="badge bg-primary">{{ $task->status }}</span>
                        </div>
                    @empty
                        <div class="text-muted">No tasks found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <strong>Blocked</strong>
                </div>
                <div class="card-body">
                    @forelse($dashboard['blocked'] as $task)
                        <div class="border-bottom py-2">
                            <div class="fw-semibold">{{ $task->title }}</div>
                            <div class="small text-muted">
                                {{ $task->project }}
                                @if(!empty($task->blocked_by)) • {{ $task->blocked_by }} @endif
                            </div>
                            @if(!empty($task->blocked_reason))
                                <div class="small mt-1">{{ $task->blocked_reason }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted">No blocked tasks.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <strong>Project Progress</strong>
                </div>
                <div class="card-body">
                    @forelse($dashboard['projects'] as $project)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ $project->project }}</span>
                                <span>{{ $project->progress }}%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $project->progress }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">No projects found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <strong>Alerts</strong>
                </div>
                <div class="card-body">
                    @forelse($dashboard['alerts'] as $alert)
                        <div class="d-flex justify-content-between align-items-start border-bottom py-2">
                            <div>
                                <div class="fw-semibold">{{ $alert->title }}</div>
                                <div class="small text-muted">{{ $alert->source }}</div>
                            </div>
                            <span class="badge text-bg-danger">{{ strtoupper($alert->severity) }}</span>
                        </div>
                    @empty
                        <div class="text-muted">No active alerts.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
