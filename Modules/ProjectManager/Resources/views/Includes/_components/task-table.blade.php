<div class="pm-card">
    <div class="pm-title-row">
        <div>
            <h3 class="pm-title-row__title">Tasks</h3>
            <div class="pm-muted">Project execution backlog, dependencies and status.</div>
        </div>
        <a href="{{ route('project_manager.tasks.create', $project) }}" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact">
            <span class="lsg-action-btn__icon"><i class="fa-solid fa-plus"></i></span>
            <span>New Task</span>
        </a>
    </div>

    <div class="pm-table-wrap">
        <table class="pm-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Schedule</th>
                    <th>Depends on</th>
                    <th style="width:170px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    <tr>
                        <td>
                            <div class="pm-table-title">
                                <strong>{{ $task->title }}</strong>
                                @if($task->comment)
                                    <span>{{ $task->comment }}</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $task->type ?: '—' }}</td>
                        <td>{{ $task->status_label }}</td>
                        <td>{{ $task->priority_label }}</td>
                        <td>
                            <div class="pm-muted">
                                @if($task->scheduled_for)
                                    {{ $task->scheduled_for->format('d/m/Y H:i') }}
                                @elseif($task->deadline)
                                    Deadline: {{ $task->deadline->format('d/m/Y H:i') }}
                                @else
                                    —
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($task->dependencies->count())
                                <div class="pm-muted">{{ $task->dependencies->pluck('title')->join(', ') }}</div>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <div class="pm-table-actions">
                                @if(!$task->is_done)
                                    <form method="POST" action="{{ route('project_manager.tasks.complete', $task) }}" class="lsg-action-form">
                                        @csrf
                                        <button type="submit" class="lsg-action-btn lsg-action-btn--success lsg-action-btn--compact" title="Complete">
                                            <span class="lsg-action-btn__icon"><i class="fa-solid fa-check"></i></span>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('project_manager.tasks.reopen', $task) }}" class="lsg-action-form">
                                        @csrf
                                        <button type="submit" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact" title="Reopen">
                                            <span class="lsg-action-btn__icon"><i class="fa-solid fa-rotate-left"></i></span>
                                        </button>
                                    </form>
                                @endif

                                <a href="{{ route('project_manager.tasks.edit', $task) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact" title="Edit">
                                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-pencil"></i></span>
                                </a>

                                <form method="POST" action="{{ route('project_manager.tasks.destroy', $task) }}" class="lsg-action-form" onsubmit="return confirm('{{ __('project-manager::project_manager.confirm_delete_task') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="lsg-action-btn lsg-action-btn--danger lsg-action-btn--compact" title="Delete">
                                        <span class="lsg-action-btn__icon"><i class="fa-solid fa-trash"></i></span>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 pm-muted">No tasks created for this project yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
