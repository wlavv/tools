@extends('layouts.app')
@section('content')
@include('roadmap-manager::partials.styles')
@include('roadmap-manager::partials.alerts')
<div class="rm-gantt-wrap rm-panel">@php($minDate = $tasks->min('planned_start_date')) @php($maxDate = $tasks->max('planned_end_date')) @php($rangeDays = max(1, optional($minDate)->diffInDays($maxDate) + 1)) @forelse($tasks as $task) @php($start = optional($task->planned_start_date)->diffInDays($minDate)) @php($duration = max(1, optional($task->planned_start_date)->diffInDays($task->planned_end_date) + 1)) @php($left = ($start / $rangeDays) * 100) @php($width = ($duration / $rangeDays) * 100)<div class="mb-3"><div class="small fw-bold">{{ $task->title }} <span class="rm-muted">({{ $task->project->name ?? '-' }})</span></div><div class="rm-gantt-bar-wrap"><div class="rm-gantt-bar" style="left:{{ $left }}%;width:{{ $width }}%"></div></div></div>@empty<div class="rm-muted">No tasks with dates found.</div>@endforelse</div>
@endsection
