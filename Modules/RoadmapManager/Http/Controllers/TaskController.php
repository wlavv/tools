<?php

namespace Modules\RoadmapManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\RoadmapManager\Models\Milestone;
use Modules\RoadmapManager\Models\Project;
use Modules\RoadmapManager\Models\TaskAttachment;
use Modules\RoadmapManager\Models\TaskComment;
use Modules\RoadmapManager\Models\TaskItem;
use Modules\RoadmapManager\Models\TaskTimeLog;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = TaskItem::with(['project', 'milestone', 'parent'])->orderBy('updated_at', 'desc')->paginate(30);
        return view('roadmap-manager::tasks.index', compact('tasks'));
    }

    public function create()
    {
        return view('roadmap-manager::tasks.form', [
            'task' => new TaskItem(),
            'projects' => Project::orderBy('name')->get(),
            'milestones' => Milestone::orderBy('name')->get(),
            'parents' => TaskItem::orderBy('title')->limit(200)->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['uuid'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();
        $task = TaskItem::create($data);
        $task->update(['path' => '/' . $task->id . '/', 'depth' => $task->parent_id ? 1 : 0]);

        return redirect()->route('roadmap.tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(TaskItem $task)
    {
        $task->load(['project', 'milestone', 'parent', 'children', 'comments', 'attachments', 'timeLogs', 'dependencies']);
        return view('roadmap-manager::tasks.show', compact('task'));
    }

    public function edit(TaskItem $task)
    {
        return view('roadmap-manager::tasks.form', [
            'task' => $task,
            'projects' => Project::orderBy('name')->get(),
            'milestones' => Milestone::orderBy('name')->get(),
            'parents' => TaskItem::where('id', '!=', $task->id)->orderBy('title')->limit(200)->get(),
        ]);
    }

    public function update(Request $request, TaskItem $task)
    {
        $task->update($this->validated($request));
        return redirect()->route('roadmap.tasks.show', $task)->with('success', 'Task updated successfully.');
    }

    public function tree()
    {
        $roots = TaskItem::with(['children.children.children', 'project', 'milestone'])
            ->whereNull('parent_id')
            ->orderBy('project_id')
            ->orderBy('sort_order')
            ->get();

        return view('roadmap-manager::tasks.tree', compact('roots'));
    }

    public function gantt()
    {
        $tasks = TaskItem::with(['project', 'milestone'])
            ->whereNotNull('planned_start_date')
            ->orderBy('planned_start_date')
            ->limit(300)
            ->get();

        return view('roadmap-manager::tasks.gantt', compact('tasks'));
    }

    public function kanban()
    {
        $statuses = ['backlog', 'todo', 'in_progress', 'in_review', 'blocked', 'completed'];
        $columns = [];
        foreach ($statuses as $status) {
            $columns[$status] = TaskItem::with('project')->where('status', $status)->orderBy('priority')->limit(100)->get();
        }
        return view('roadmap-manager::tasks.kanban', compact('columns'));
    }

    public function storeComment(Request $request, TaskItem $task)
    {
        $data = $request->validate(['content' => 'required|string']);
        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'content' => $data['content'],
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    public function storeTimeLog(Request $request, TaskItem $task)
    {
        $data = $request->validate([
            'logged_hours' => 'required|numeric|min:0.25',
            'log_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        TaskTimeLog::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'logged_hours' => $data['logged_hours'],
            'log_date' => $data['log_date'],
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('success', 'Time log added successfully.');
    }

    public function storeAttachment(Request $request, TaskItem $task)
    {
        $request->validate(['file' => 'required|file|max:20480']);

        $file = $request->file('file');
        $path = $file->store('roadmap/tasks', 'public');

        TaskAttachment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'disk' => 'public',
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);

        return back()->with('success', 'Attachment uploaded successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'parent_id' => 'nullable|integer|exists:wt_task_items,id',
            'project_id' => 'required|integer|exists:wt_projects,id',
            'milestone_id' => 'nullable|integer|exists:wt_milestones,id',
            'level' => 'nullable|integer|min:1|max:9',
            'code' => 'nullable|string|max:30',
            'title' => 'required|string|max:300',
            'description' => 'nullable|string',
            'status' => 'required|in:backlog,todo,in_progress,in_review,blocked,completed,cancelled,deferred',
            'priority' => 'required|in:low,medium,high,critical',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'logged_hours' => 'nullable|numeric|min:0',
            'remaining_hours' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|integer',
            'risk_level' => 'nullable|in:none,low,medium,high,critical',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}
