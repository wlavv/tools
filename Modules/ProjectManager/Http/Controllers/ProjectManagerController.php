<?php
namespace Modules\ProjectManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\ProjectManager\Http\Requests\StoreProjectManagerRequest;
use Modules\ProjectManager\Http\Requests\StoreProjectTaskRequest;
use Modules\ProjectManager\Http\Requests\UpdateProjectManagerRequest;
use Modules\ProjectManager\Http\Requests\UpdateProjectTaskRequest;
use Modules\ProjectManager\Models\ProjectManager;
use Modules\ProjectManager\Models\ProjectTask;
use Modules\ProjectManager\Services\ProjectManagerService;

class ProjectManagerController extends Controller
{
    public function __construct(protected ProjectManagerService $service)
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View
    {
        return $this->view('project-manager::index', [
            'groups' => $this->service->getGroups(),
            'stats' => $this->service->getStats(),
            'recommendedTasks' => $this->service->getRecommendedTasks(8),
        ]);
    }

    public function create(): View
    {
        return $this->view('project-manager::pages.create', [
            'statuses' => config('project-manager.project_statuses', []),
            'projects' => ProjectManager::where('id_parent', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProjectManagerRequest $request): RedirectResponse
    {
        $project = $this->service->store($request->validated());
        return redirect()->route('project_manager.show', $project)->with('success', __('project-manager::project_manager.created_success'));
    }

    public function show(ProjectManager $project): View
    {
        $project->load([
            'tasks' => fn ($query) => $query->with('dependencies')->orderBy('execution_order')->orderBy('priority')->orderBy('id'),
            'children.tasks',
        ]);

        return $this->view('project-manager::pages.show', [
            'project' => $project,
            'tasks' => $project->tasks,
            'taskStatuses' => config('project-manager.task_statuses', []),
            'taskPriorities' => config('project-manager.task_priorities', []),
        ]);
    }

    public function edit(ProjectManager $project): View
    {
        return $this->view('project-manager::pages.edit', [
            'project' => $project,
            'statuses' => config('project-manager.project_statuses', []),
            'projects' => ProjectManager::where('id_parent', 0)
                ->where('id', '!=', $project->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateProjectManagerRequest $request, ProjectManager $project): RedirectResponse
    {
        $this->service->update($project, $request->validated());
        return redirect()->route('project_manager.show', $project)->with('success', __('project-manager::project_manager.updated_success'));
    }

    public function destroy(ProjectManager $project): RedirectResponse
    {
        foreach ($project->children as $child) {
            $child->delete();
        }

        $project->tasks()->delete();
        $project->delete();

        return redirect()->route('project_manager.index')->with('success', __('project-manager::project_manager.deleted_success'));
    }

    public function createTask(ProjectManager $project): View
    {
        return $this->view('project-manager::pages.tasks.form', [
            'project' => $project,
            'task' => null,
            'action' => route('project_manager.tasks.store', $project),
            'method' => 'POST',
            'taskStatuses' => config('project-manager.task_statuses', []),
            'taskPriorities' => config('project-manager.task_priorities', []),
            'taskTypes' => config('project-manager.task_types', []),
            'parentTasks' => $this->service->getProjectTaskOptions($project),
            'dependencyOptions' => $this->service->getProjectTaskOptions($project),
            'selectedDependencies' => [],
        ]);
    }

    public function storeTask(StoreProjectTaskRequest $request, ProjectManager $project): RedirectResponse
    {
        $task = $this->service->createTask($project, $request->validated());

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', __('project-manager::project_manager.task_created_success'));
    }

    public function editTask(ProjectTask $task): View
    {
        $task->load('project', 'dependencies');

        return $this->view('project-manager::pages.tasks.form', [
            'project' => $task->project,
            'task' => $task,
            'action' => route('project_manager.tasks.update', $task),
            'method' => 'PUT',
            'taskStatuses' => config('project-manager.task_statuses', []),
            'taskPriorities' => config('project-manager.task_priorities', []),
            'taskTypes' => config('project-manager.task_types', []),
            'parentTasks' => $this->service->getProjectTaskOptions($task->project, $task),
            'dependencyOptions' => $this->service->getProjectTaskOptions($task->project, $task),
            'selectedDependencies' => $task->dependencies->pluck('id')->map(fn ($id) => (int) $id)->all(),
        ]);
    }

    public function updateTask(UpdateProjectTaskRequest $request, ProjectTask $task): RedirectResponse
    {
        $this->service->updateTask($task, $request->validated());

        return redirect()
            ->route('project_manager.show', $task->project)
            ->with('success', __('project-manager::project_manager.task_updated_success'));
    }

    public function destroyTask(ProjectTask $task): RedirectResponse
    {
        $project = $task->project;
        $this->service->deleteTask($task);

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', __('project-manager::project_manager.task_deleted_success'));
    }

    public function completeTask(ProjectTask $task): RedirectResponse
    {
        $this->service->completeTask($task);

        return redirect()
            ->route('project_manager.show', $task->project)
            ->with('success', __('project-manager::project_manager.task_completed_success'));
    }

    public function reopenTask(ProjectTask $task): RedirectResponse
    {
        $this->service->reopenTask($task);

        return redirect()
            ->route('project_manager.show', $task->project)
            ->with('success', __('project-manager::project_manager.task_reopened_success'));
    }
}
