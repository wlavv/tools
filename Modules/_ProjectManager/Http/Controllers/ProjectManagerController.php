<?php
namespace Modules\ProjectManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\ProjectManager\Http\Requests\StoreProjectManagerRequest;
use Modules\ProjectManager\Http\Requests\UpdateProjectManagerRequest;
use Modules\ProjectManager\Models\ProjectManager;
use Modules\ProjectManager\Services\ProjectManagerService;

class ProjectManagerController extends Controller
{
    public function __construct(protected ProjectManagerService $service){

        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(): View{

        return $this->view('project-manager::index', [
            'groups' => $this->service->getGroups(),
            'stats' => $this->service->getStats(),
        ]);
    }

    public function create(): View{

        return $this->view('project-manager::pages.create', [
            'statuses' => config('project-manager.project_statuses', []),
            'projects' => ProjectManager::where('id_parent', 0)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreProjectManagerRequest $request): RedirectResponse{

        $project = $this->service->store($request->validated());
        return redirect()->route('project_manager.show', $project)->with('success', __('project-manager::project_manager.created_success'));
    }

    public function show(ProjectManager $project): View{
        return $this->view('project-manager::pages.show', ['project' => $project]);
    }

    public function edit(ProjectManager $project): View{

        return $this->view('project-manager::pages.edit', [
            'project' => $project,
            'statuses' => config('project-manager.project_statuses', []),
            'projects' => ProjectManager::where('id_parent', 0)
                ->where('id', '!=', $project->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(UpdateProjectManagerRequest $request, ProjectManager $project): RedirectResponse{

        $this->service->update($project, $request->validated());
        return redirect()->route('project_manager.show', $project)->with('success', __('project-manager::project_manager.updated_success'));
    }

    public function destroy(ProjectManager $project): RedirectResponse{

        foreach ($project->children as $child) $child->delete();
        $project->delete();

        return redirect()->route('project_manager.index')->with('success', __('project-manager::project_manager.deleted_success'));
    }
}
