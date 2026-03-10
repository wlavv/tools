<div class="pm-task">
    <div class="pm-task-head">
        <div>
            <strong>{{ $task->title }}</strong>
            <div class="pm-task-meta">
                Status: {{ $task->status }} |
                Priority: {{ $task->priority }} |
                Parent: {{ $task->id_parent }}
            </div>
            @if($task->comment)
                <div class="mt-2">{{ $task->comment }}</div>
            @endif
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <form method="POST" action="{{ route('project_manager.tasks.toggle', [$project, $task]) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-success">{{ $task->status === 'Done' ? 'Reabrir' : 'Concluir' }}</button>
            </form>
            <form method="POST" action="{{ route('project_manager.tasks.destroy', [$project, $task]) }}" onsubmit="return confirm('Apagar task?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger">Apagar</button>
            </form>
        </div>
    </div>

    <details class="mt-3">
        <summary>Editar task</summary>
        <form method="POST" action="{{ route('project_manager.tasks.update', [$project, $task]) }}" class="mt-3">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Título</label>
                    <input type="text" name="title" class="form-control" value="{{ $task->title }}" required>
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        @foreach($taskStatuses as $status)
                            <option value="{{ $status }}" @selected($task->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Prioridade</label>
                    <input type="number" min="0" name="priority" class="form-control" value="{{ $task->priority }}">
                </div>
                <div class="col-md-4">
                    <label>Parent</label>
                    <select name="id_parent" class="form-control">
                        <option value="0" @selected((int)$task->id_parent === 0)>Root</option>
                        @foreach($project->tasks as $taskOption)
                            @if($taskOption->id !== $task->id)
                                <option value="{{ $taskOption->id }}" @selected((int)$task->id_parent === (int)$taskOption->id)>{{ $taskOption->title }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Start date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ optional($task->start_date)->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label>Expected time</label>
                    <input type="number" min="0" name="expected_time" class="form-control" value="{{ $task->expected_time }}">
                </div>
                <div class="col-md-12">
                    <label>Comentário</label>
                    <textarea name="comment" class="form-control" rows="3">{{ $task->comment }}</textarea>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary btn-sm" type="submit">Atualizar task</button>
            </div>
        </form>
    </details>

    @if($task->children->count())
        <div class="pm-task-children">
            @foreach($task->children as $child)
                @include('project-manager::Includes._components.task-item', ['task' => $child, 'project' => $project, 'taskStatuses' => $taskStatuses, 'level' => ($level ?? 0) + 1])
            @endforeach
        </div>
    @endif
</div>
