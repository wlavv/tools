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
    public function __construct(protected ProjectManagerService $service)
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        return view('project-manager::index', ['groups' => $this->service->getGroups(), 'stats' => $this->service->getStats()]);
    }

    public function create(): View
    {
        return view('project-manager::pages.create', ['statuses' => config('project-manager.project_statuses', []), 'projects' => ProjectManager::where('id_parent',0)->orderBy('name')->get()]);
    }

    public function store(StoreProjectManagerRequest $request): RedirectResponse
    {
        $project = $this->service->store($request->validated());
        return redirect()->route('project_manager.show',$project)->with('success','Projeto criado com sucesso.');
    }

    public function show(ProjectManager $project): View
    {
        return view('project-manager::pages.show', compact('project'));
    }

    public function edit(ProjectManager $project): View
    {
        return view('project-manager::pages.edit', ['project' => $project, 'statuses' => config('project-manager.project_statuses', []), 'projects' => ProjectManager::where('id_parent',0)->where('id','!=',$project->id)->orderBy('name')->get()]);
    }

    public function update(UpdateProjectManagerRequest $request, ProjectManager $project): RedirectResponse
    {
        $this->service->update($project, $request->validated());
        return redirect()->route('project_manager.show',$project)->with('success','Projeto atualizado com sucesso.');
    }

    public function destroy(ProjectManager $project): RedirectResponse
    {
        foreach ($project->children as $child) { $child->delete(); }
        $project->delete();
        return redirect()->route('project_manager.index')->with('success','Projeto removido com sucesso.');
    }
}
