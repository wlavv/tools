<?php

namespace Modules\RoadmapManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\RoadmapManager\Models\Project;
use Modules\RoadmapManager\Models\ProjectGroup;

class RoadmapProjectController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): View
    {
        return $this->view('roadmap-manager::projects.index', [
            'projects' => Project::with('roadmapGroups')->orderBy('updated_at', 'desc')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->view('roadmap-manager::projects.form', [
            'project' => new Project(),
            'groups' => ProjectGroup::orderBy('sort_order')->get(),
            'selectedGroups' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:15',
            'priority' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'website' => 'nullable|string|max:500',
            'group_ids' => 'array',
            'group_ids.*' => 'integer|exists:wt_roadmap_groups,id',
        ]);

        $project = Project::create([
            'id_parent' => 0,
            'have_details' => 0,
            'name' => $data['name'],
            'status' => $data['status'] ?? '1',
            'priority' => $data['priority'] ?? null,
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'website' => $data['website'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'deadline' => $data['deadline'] ?? null,
        ]);

        foreach (($data['group_ids'] ?? []) as $groupId) {
            DB::table('wt_project_group_links')->updateOrInsert(
                ['project_id' => $project->id, 'roadmap_group_id' => $groupId],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        return redirect()->route('roadmap_manager.projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load(['roadmapGroups', 'milestones', 'tasks']);
        return $this->view('roadmap-manager::projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $groups = ProjectGroup::orderBy('sort_order')->get();
        $selectedGroups = $project->roadmapGroups()->pluck('wt_roadmap_groups.id')->toArray();

        return $this->view('roadmap-manager::projects.form', compact('project', 'groups', 'selectedGroups'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string|max:15',
            'priority' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'website' => 'nullable|string|max:500',
            'group_ids' => 'array',
            'group_ids.*' => 'integer|exists:wt_roadmap_groups,id',
        ]);

        $project->update([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? $project->status,
            'priority' => $data['priority'] ?? null,
            'website' => $data['website'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'deadline' => $data['deadline'] ?? null,
        ]);

        DB::table('wt_project_group_links')->where('project_id', $project->id)->delete();
        foreach (($data['group_ids'] ?? []) as $groupId) {
            DB::table('wt_project_group_links')->updateOrInsert(
                ['project_id' => $project->id, 'roadmap_group_id' => $groupId],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }

        return redirect()->route('roadmap_manager.projects.index')->with('success', 'Project updated successfully.');
    }
}
