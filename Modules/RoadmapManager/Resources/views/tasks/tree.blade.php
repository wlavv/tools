@extends('roadmap-manager::layouts.page')

@section('roadmap-content')
<h1 class="h3 mb-3">Task Tree</h1>
<div class="card shadow-sm">
    <div class="card-body">
        @php
            $renderNode = function($task, $level = 0) use (&$renderNode) {
                echo '<div style="margin-left:' . ($level * 24) . 'px" class="mb-2">';
                echo '<span class="badge bg-secondary me-2">L' . e($task->level) . '</span>';
                echo '<a href="' . route('roadmap.tasks.show', $task->id) . '">' . e($task->title) . '</a>';
                echo ' <small class="text-muted">(' . e($task->status) . ')</small>';
                echo '</div>';
                foreach ($task->children as $child) {
                    $renderNode($child, $level + 1);
                }
            };
        @endphp
        @forelse($roots as $root)
            {!! $renderNode($root) !!}
        @empty
            <div class="text-muted">No tasks found.</div>
        @endforelse
    </div>
</div>
@endsection
