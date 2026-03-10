<?php

namespace Modules\ProjectManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\ProjectManager\Http\Requests\StoreProjectManagerRequest;
use Modules\ProjectManager\Http\Requests\UpdateProjectManagerRequest;
use Modules\ProjectManager\Http\Requests\StoreProjectTaskRequest;
use Modules\ProjectManager\Http\Requests\UpdateProjectTaskRequest;
use Modules\ProjectManager\Models\ProjectManager;
use Modules\ProjectManager\Models\ProjectTask;
use Modules\ProjectManager\Services\ProjectManagerService;

class ProjectManagerController extends Controller
{
    public function __construct(
        protected ProjectManagerService $service
    ) {
        $this->middleware('auth');
    }

    public function index(): View
    {
        return view('project-manager::Index', [
            'projects' => $this->service->getIndexData(),
            'stats' => $this->service->getStats(),
            'statuses' => config('project-manager.project_statuses', []),
        ]);
    }

    public function create(): View
    {
        return view('project-manager::pages.create', [
            'statuses' => config('project-manager.project_statuses', []),
            'projects' => ProjectManager::orderBy('name')->get(),
        ]);
    }

    public function store(StoreProjectManagerRequest $request): RedirectResponse
    {
        $project = $this->service->createProject($request->validated());

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', 'Projeto criado com sucesso.');
    }

    public function show(ProjectManager $project): View
    {
        return view('project-manager::pages.show', [
            'project' => $this->service->getProjectDetail($project),
            'statuses' => config('project-manager.project_statuses', []),
            'taskStatuses' => config('project-manager.task_statuses', []),
            'projects' => ProjectManager::where('id', '!=', $project->id)->orderBy('name')->get(),
        ]);
    }

    public function edit(ProjectManager $project): View
    {
        return view('project-manager::pages.edit', [
            'project' => $project,
            'statuses' => config('project-manager.project_statuses', []),
            'projects' => ProjectManager::where('id', '!=', $project->id)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateProjectManagerRequest $request, ProjectManager $project): RedirectResponse
    {
        $this->service->updateProject($project, $request->validated());

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', 'Projeto atualizado com sucesso.');
    }

    public function destroy(ProjectManager $project): RedirectResponse
    {
        $this->service->deleteProject($project);

        return redirect()
            ->route('project_manager.index')
            ->with('success', 'Projeto removido com sucesso.');
    }

    public function storeTask(StoreProjectTaskRequest $request, ProjectManager $project): RedirectResponse
    {
        $this->service->createTask($project, $request->validated());

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', 'Task criada com sucesso.');
    }

    public function updateTask(UpdateProjectTaskRequest $request, ProjectManager $project, ProjectTask $task): RedirectResponse
    {
        $this->service->updateTask($project, $task, $request->validated());

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', 'Task atualizada com sucesso.');
    }

    public function destroyTask(ProjectManager $project, ProjectTask $task): RedirectResponse
    {
        $this->service->deleteTask($project, $task);

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', 'Task removida com sucesso.');
    }

    public function toggleTask(ProjectManager $project, ProjectTask $task): RedirectResponse
    {
        $this->service->toggleTask($project, $task);

        return redirect()
            ->route('project_manager.show', $project)
            ->with('success', 'Estado da task atualizado.');
    }
}
