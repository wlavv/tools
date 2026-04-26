<?php

namespace Modules\ProjectManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\ProjectManager\Http\Requests\ProjectTaskRequest;
use Modules\ProjectManager\Models\ProjectManager;
use Modules\ProjectManager\Models\ProjectTask;

class ProjectTaskController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function create(ProjectManager $project): View
    {
        $parentTask = request('parent')
            ? ProjectTask::forProject($project->id)->find(request('parent'))
            : null;

        return $this->view('project-manager::tasks.form', [
            'project' => $project,
            'task' => new ProjectTask([
                'id_project' => $project->id,
                'id_parent' => $parentTask?->id ?? 0,
                'priority' => ProjectTask::PRIORITY_NORMAL,
                'status' => ProjectTask::STATUS_TODO,
                'start_date' => now(),
            ]),
            'parentTask' => $parentTask,
            'availableTasks' => ProjectTask::forProject($project->id)->orderBy('execution_order')->orderBy('title')->get(),
            'action' => route('project_manager.tasks.store', $project),
            'method' => 'POST',
        ]);
    }

    public function store(ProjectTaskRequest $request, ProjectManager $project): RedirectResponse
    {
        $data = $request->validated();
        $data['id_project'] = $project->id;
        $data['id_parent'] = (int) ($data['id_parent'] ?? 0);

        $task = ProjectTask::create($data);
        $task->syncDependenciesSafe($data['dependencies'] ?? []);

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', __('project-manager::tasks.created_successfully'));
    }

    public function edit(ProjectManager $project, ProjectTask $task): View
    {
        abort_unless((int) $task->id_project === (int) $project->id, 404);

        return $this->view('project-manager::tasks.form', [
            'project' => $project,
            'task' => $task,
            'parentTask' => (int) $task->id_parent > 0 ? $task->parent : null,
            'availableTasks' => ProjectTask::forProject($project->id)
                ->where('id', '!=', $task->id)
                ->orderBy('execution_order')
                ->orderBy('title')
                ->get(),
            'action' => route('project_manager.tasks.update', [$project, $task]),
            'method' => 'PUT',
        ]);
    }

    public function update(ProjectTaskRequest $request, ProjectManager $project, ProjectTask $task): RedirectResponse
    {
        abort_unless((int) $task->id_project === (int) $project->id, 404);

        $data = $request->validated();
        $data['id_project'] = $project->id;
        $data['id_parent'] = (int) ($data['id_parent'] ?? 0);

        if ((int) $data['id_parent'] === (int) $task->id) {
            return back()->withErrors(['id_parent' => 'A tarefa não pode ser pai dela própria.'])->withInput();
        }

        $task->update($data);
        $task->syncDependenciesSafe($data['dependencies'] ?? []);

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', __('project-manager::tasks.updated_successfully'));
    }

    public function destroy(ProjectManager $project, ProjectTask $task): RedirectResponse
    {
        abort_unless((int) $task->id_project === (int) $project->id, 404);

        if (ProjectTask::dependenciesTableReady()) {
            $task->dependencies()->detach();
            $task->dependents()->detach();
        }
        $task->delete();

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', __('project-manager::tasks.deleted_successfully'));
    }

    public function complete(ProjectManager $project, ProjectTask $task): RedirectResponse
    {
        abort_unless((int) $task->id_project === (int) $project->id, 404);

        $task->update([
            'status' => ProjectTask::STATUS_DONE,
            'completed_at' => now(),
        ]);

        return back()->with('success', __('project-manager::tasks.completed_successfully'));
    }

    public function reopen(ProjectManager $project, ProjectTask $task): RedirectResponse
    {
        abort_unless((int) $task->id_project === (int) $project->id, 404);

        $task->update([
            'status' => ProjectTask::STATUS_TODO,
            'completed_at' => null,
        ]);

        return back()->with('success', __('project-manager::tasks.reopened_successfully'));
    }
}
