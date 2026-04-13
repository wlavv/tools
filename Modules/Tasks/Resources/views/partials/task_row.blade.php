<label class="fp-task-row"
       style="display:flex;align-items:center;gap:12px;width:100%;min-width:0;padding:10px 12px;border-radius:5px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.22);">

    {{-- esconder checkbox --}}
    <span class="fp-task-checkmark" style="display:none;"></span>

    @if(!empty($task->image))
        <span class="fp-task-image"
              style="flex:0 0 52px;width:52px;height:52px;overflow:hidden;border-radius:5px;border:1px solid rgba(255,255,255,.24);background:rgba(255,255,255,.18);">
            <img src="{{ asset('modules/tasks/tasks/' . ltrim($task->image, '/')) }}"
                 alt="{{ $task->title }}"
                 style="width:100%;height:100%;object-fit:cover;">
        </span>
    @endif

    <span class="fp-task-title"
          style="flex:1;min-width:0;{{ ($task->response_status ?? 0) === 1 ? 'text-decoration:line-through;opacity:.7;' : '' }}">
        {{ $task->title }}
    </span>

    {{-- ESTADO --}}
    @if(($task->response_status ?? 0) === 1)
        <span style="color:#4f9b72;margin-left:auto;font-weight:600;">
            Respondido
        </span>

    @elseif(($task->response_status ?? 0) === -1)
        <span style="color:#c96f6f;margin-left:auto;font-weight:600;">
            Respondido
        </span>

    @else
        <span style="display:flex;gap:16px;margin-left:auto;">

            <span style="font-size:42px;color:#4f9b72;cursor:pointer;"
                  data-task-id="{{ $task->id }}"
                  data-task-title="{{ $task->title }}"
                  data-task-date="{{ request('date') }}"
                  data-task-state="1"
                  onclick="familyPlanner.confirmTaskState(this)">
                <i class="fa-solid fa-circle-check"></i>
            </span>

            <span style="font-size:42px;color:#c96f6f;cursor:pointer;"
                  data-task-id="{{ $task->id }}"
                  data-task-title="{{ $task->title }}"
                  data-task-date="{{ request('date') }}"
                  data-task-state="-1"
                  onclick="familyPlanner.confirmTaskState(this)">
                <i class="fa-solid fa-circle-xmark"></i>
            </span>

        </span>
    @endif

</label>