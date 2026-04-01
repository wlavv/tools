@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<h1 class="h3 mb-3">Gantt View</h1>
<div class="card shadow-sm">
    <div class="card-body">
        <div class="small text-muted mb-3">Basic visual Gantt based on planned start/end dates.</div>
        @php
            $all = $tasks->filter(fn($t) => $t->planned_start_date && ($t->planned_end_date || $t->deadline));
            $min = $all->min('planned_start_date');
            $max = $all->max(fn($t) => $t->planned_end_date ?: $t->deadline);
            $rangeDays = $min && $max ? max(1, \Carbon\Carbon::parse($min)->diffInDays(\Carbon\Carbon::parse($max)) + 1) : 1;
        @endphp

        @forelse($tasks as $task)
            @php
                $start = $task->planned_start_date ? \Carbon\Carbon::parse($task->planned_start_date) : null;
                $end = ($task->planned_end_date ?: $task->deadline) ? \Carbon\Carbon::parse($task->planned_end_date ?: $task->deadline) : null;
                $offset = ($start && $min) ? \Carbon\Carbon::parse($min)->diffInDays($start) : 0;
                $duration = ($start && $end) ? max(1, $start->diffInDays($end) + 1) : 1;
                $left = ($offset / $rangeDays) * 100;
                $width = ($duration / $rangeDays) * 100;
            @endphp
            <div class="mb-3">
                <div class="small fw-bold">{{ $task->title }} <span class="text-muted">({{ $task->project->name ?? '-' }})</span></div>
                <div style="position:relative;background:#f1f3f5;height:28px;border-radius:6px;overflow:hidden;">
                    <div style="position:absolute;left:{{ $left }}%;width:{{ $width }}%;top:4px;bottom:4px;background:#0d6efd;border-radius:4px;"></div>
                </div>
            </div>
        @empty
            <div class="text-muted">No tasks with dates found.</div>
        @endforelse
    </div>
</div>
@endsection
