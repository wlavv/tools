@php
    $level = $level ?? 0;
@endphp

<div class="pm-task-tree" style="--task-level: {{ $level }}">
    @forelse($tasks as $task)
        <div class="pm-task-node pm-task-status-{{ $task->statusCssClass() }}">
            <div class="pm-task-row">
                <div class="pm-task-main">
                    <div class="pm-task-check">
                        @if($task->isDone())
                            <i class="fa-solid fa-circle-check"></i>
                        @elseif($task->isBlockedByDependencies())
                            <i class="fa-solid fa-circle-exclamation"></i>
                        @else
                            <i class="fa-regular fa-circle"></i>
                        @endif
                    </div>

                    <div class="pm-task-content">
                        <div class="pm-task-title">
                            <strong>#{{ $task->id }} — {{ $task->title }}</strong>
                            <span class="pm-status-pill pm-status-pill--{{ $task->statusCssClass() }}">{{ $task->statusLabel() }}</span>
                        </div>

                        <div class="pm-task-meta">
                            <span><i class="fa-solid fa-flag"></i> {{ $task->priorityLabel() }}</span>
                            @if($task->expected_time)
                                <span><i class="fa-regular fa-clock"></i> {{ $task->expected_time }} min</span>
                            @endif
                            @if($task->deadline)
                                <span><i class="fa-regular fa-calendar"></i> {{ __('project-manager::tasks.deadline') }}: {{ $task->deadline->format('d/m/Y') }}</span>
                            @endif
                            <span><i class="fa-solid fa-chart-simple"></i> {{ $task->progress_percent }}%</span>
                        </div>

                        @if($task->comment)
                            <div class="pm-task-comment">{{ $task->comment }}</div>
                        @endif
                    </div>
                </div>

                <div class="pm-task-actions">
                    <a href="{{ route('project_manager.tasks.create', [$project, 'parent' => $task->id]) }}"
                       class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact"
                       title="{{ __('project-manager::tasks.new_task') }}">
                        <span class="lsg-action-btn__icon"><i class="fa-solid fa-plus"></i></span>
                    </a>

                    <a href="{{ route('project_manager.tasks.edit', [$project, $task]) }}"
                       class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact"
                       title="{{ __('project-manager::tasks.edit') }}">
                        <span class="lsg-action-btn__icon"><i class="fa-solid fa-pencil"></i></span>
                    </a>

                    @if($task->isDone())
                        <form method="POST" action="{{ route('project_manager.tasks.reopen', [$project, $task]) }}" class="lsg-action-form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" title="{{ __('project-manager::tasks.reopen_task') }}">
                                <span class="lsg-action-btn__icon"><i class="fa-solid fa-rotate-left"></i></span>
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('project_manager.tasks.complete', [$project, $task]) }}" class="lsg-action-form">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact" title="{{ __('project-manager::tasks.complete_task') }}">
                                <span class="lsg-action-btn__icon"><i class="fa-solid fa-check"></i></span>
                            </button>
                        </form>
                    @endif

                    <form method="POST"
                          action="{{ route('project_manager.tasks.destroy', [$project, $task]) }}"
                          class="lsg-action-form"
                          onsubmit="return confirm('{{ __('project-manager::tasks.confirm_delete') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="lsg-action-btn lsg-action-btn--danger lsg-action-btn--compact" title="{{ __('project-manager::tasks.delete') }}">
                            <span class="lsg-action-btn__icon"><i class="fa-solid fa-trash"></i></span>
                        </button>
                    </form>
                </div>
            </div>

            @if($task->childrenRecursive->isNotEmpty())
                @include('project-manager::tasks.partials.task-tree', [
                    'tasks' => $task->childrenRecursive,
                    'project' => $project,
                    'level' => $level + 1,
                ])
            @endif
        </div>
    @empty
        <div class="pm-empty-state">
            <strong>{{ __('project-manager::tasks.no_tasks') }}</strong>
        </div>
    @endforelse
</div>
