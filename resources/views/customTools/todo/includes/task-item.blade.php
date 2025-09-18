<li class="task-item" 
    id="todoTask-{{ $task->id }}"
    data-id="{{ $task->id }}"
    data-title="{{ $task->title }}"
    data-start_date="{{ $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') : '' }}"
    data-expected_time="{{ $task->expected_time }}"
    data-comment="{{ $task->comment }}"
    data-id_parent="{{ $task->id_parent }}"
>
    <div class="task-main">
        <div class="task-actions">
            <button class="btn" style="padding: 4px 5px;color: darkgreen;" onclick="event.stopPropagation(); addSubTask({{$task->id}})">
                <i class="fa-solid fa-plus"></i>
            </button>
        </div>
        <i class="fa-solid fa-arrows-up-down-left-right drag-handle" style="margin-top: 5px;"></i>
        <span onclick="editTask(this)" class="task-title">{{ $task->title }}</span>
        @if(isset($task->children) && count($task->children) > 0)
        
        @else
            <i onclick="event.stopPropagation(); setTaskAsDone({{$task->id}})" class="fa-solid fa-check" style="margin-top: 5px; color: green; margin-right: 10px;float: right;"></i>
        @endif
    </div>

    <ul class="task-list @if(isset($task->children) && count($task->children) < 1) toHide @endif">
        @if(isset($task->children) && count($task->children) > 0)
            @foreach($task->children as $child)
                @include('customTools.todo.includes.task-item', ['task' => $child])
            @endforeach
        @endif
        <li class="drop-placeholder">Drop here</li>
    </ul>
</li>
