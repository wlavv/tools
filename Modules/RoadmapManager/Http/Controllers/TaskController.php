<?php

namespace Modules\RoadmapManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\RoadmapManager\Models\Milestone;
use Modules\RoadmapManager\Models\Project;
use Modules\RoadmapManager\Models\TaskAttachment;
use Modules\RoadmapManager\Models\TaskComment;
use Modules\RoadmapManager\Models\TaskItem;
use Modules\RoadmapManager\Models\TaskTimeLog;

class TaskController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): View
    {
        $tasks = TaskItem::with(['project', 'milestone', 'parent'])->orderBy('updated_at', 'desc')->paginate(30);
        return $this->view('roadmap-manager::tasks.index', compact('tasks'));
    }

    public function create(): View
    {
        return $this->view('roadmap-manager::tasks.form', [
            'task' => new TaskItem(),
            'projects' => Project::orderBy('name')->get(),
            'milestones' => Milestone::orderBy('name')->get(),
            'parents' => TaskItem::orderBy('title')->limit(200)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['uuid'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();
        $task = TaskItem::create($data);
        $task->update(['path' => '/' . $task->id . '/', 'depth' => $task->parent_id ? 1 : 0]);

        return redirect()->route('roadmap_manager.tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(TaskItem $task): View
    {
        $task->load(['project', 'milestone', 'parent', 'children', 'comments', 'attachments', 'timeLogs', 'dependencies']);
        return $this->view('roadmap-manager::tasks.show', compact('task'));
    }

    public function edit(TaskItem $task): View
    {
        return $this->view('roadmap-manager::tasks.form', [
            'task' => $task,
            'projects' => Project::orderBy('name')->get(),
            'milestones' => Milestone::orderBy('name')->get(),
            'parents' => TaskItem::where('id', '!=', $task->id)->orderBy('title')->limit(200)->get(),
        ]);
    }

    public function update(Request $request, TaskItem $task): RedirectResponse
    {
        $task->update($this->validated($request));

        return redirect()->route('roadmap_manager.tasks.show', $task)->with('success', 'Task updated successfully.');
    }

    public function tree(): View
    {
        $roots = TaskItem::with(['children.children.children', 'project', 'milestone'])
            ->whereNull('parent_id')
            ->orderBy('project_id')
            ->orderBy('sort_order')
            ->get();

        return $this->view('roadmap-manager::tasks.tree', compact('roots'));
    }

    public function gantt(): View
    {
        $tasks = TaskItem::with(['project', 'milestone'])
            ->whereNotNull('planned_start_date')
            ->orderBy('planned_start_date')
            ->limit(300)
            ->get();

        return $this->view('roadmap-manager::tasks.gantt', compact('tasks'));
    }

    public function kanban(): View
    {
        $statuses = ['backlog', 'todo', 'in_progress', 'in_review', 'blocked', 'completed'];
        $columns = [];
        foreach ($statuses as $status) {
            $columns[$status] = TaskItem::with('project')->where('status', $status)->orderBy('priority')->limit(100)->get();
        }

        return $this->view('roadmap-manager::tasks.kanban', compact('columns'));
    }

    public function storeComment(Request $request, TaskItem $task): RedirectResponse
    {
        $data = $request->validate(['content' => 'required|string']);
        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => auth()->id(),
            'content' => $data['content'],
        ]);

        return back()->with('success', 'Comment added successfully.');
    }

    public function storeTimeLog(Request $request, TaskItem $task): RedirectResponse
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

    public function storeAttachment(Request $request, TaskItem $task): RedirectResponse
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

    protected function validated(Request $request): array
    {
        return $request->validate([
            'project_id' => 'required|integer|exists:wt_projects,id',
            'milestone_id' => 'nullable|integer|exists:wt_milestones,id',
            'parent_id' => 'nullable|integer|exists:wt_task_items,id',
            'type' => 'required|in:main_task,task,mini_task,micro_task',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:backlog,todo,in_progress,in_review,blocked,completed,cancelled',
            'priority' => 'required|integer|min:1|max:5',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'planned_hours' => 'nullable|numeric|min:0',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}
