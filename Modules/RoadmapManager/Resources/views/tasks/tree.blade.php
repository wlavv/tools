@extends('layouts.app')

@section('content')
    @include('roadmap-manager::partials.styles')
    @include('roadmap-manager::partials.alerts')

    <div class="rm-tree-wrap rm-panel">
        <div>
            @php
                $renderNode = function ($task, $level = 0) use (&$renderNode) {
                    echo '<div style="margin-left:' . ($level * 24) . 'px" class="mb-2">';
                    echo '<span class="badge bg-secondary me-2">L' . e($task->level) . '</span>';
                    echo '<a href="' . route('roadmap_manager.tasks.show', $task->id) . '">' . e($task->title) . '</a>';
                    echo ' <small class="rm-muted">(' . e($task->status) . ')</small>';
                    echo '</div>';

                    foreach ($task->children as $child) {
                        $renderNode($child, $level + 1);
                    }
                };
            @endphp

            @forelse ($roots as $root)
                {!! $renderNode($root) !!}
            @empty
                <div class="rm-muted">No tasks found.</div>
            @endforelse
        </div>
    </div>
@endsection
