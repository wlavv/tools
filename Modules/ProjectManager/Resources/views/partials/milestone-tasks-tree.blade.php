@php use Illuminate\Support\Str; @endphp
@if($tasks->count())
    <div class="pm-tree-list">
        @foreach($tasks as $task)
            <div class="pm-tree-row">
                <div class="pm-tree-main">
                    <span class="pm-status-dot pm-status-{{ str_replace('_','-', $task->status ?? 'pending') }}"></span>
                    <div>
                        <strong>{{ $task->title ?? 'Task #' . $task->id }}</strong>
                        <div class="pm-muted pm-small">{{ $task->type ?? 'task' }} · {{ Str::limit($task->description ?? $task->comment ?? '', 90) }}</div>
                    </div>
                </div>
                <div class="pm-tree-meta">
                    <span class="pm-pill {{ ($task->status ?? '') === 'blocked' ? 'pm-pill--danger' : (($task->status ?? '') === 'in_progress' ? 'pm-pill--gold' : '') }}">{{ $task->status ?? 'pending' }}</span>
                    @if(!empty($task->deadline))<span class="pm-muted pm-small">{{ $task->deadline }}</span>@endif
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="pm-empty">Sem tasks neste milestone.</div>
@endif
