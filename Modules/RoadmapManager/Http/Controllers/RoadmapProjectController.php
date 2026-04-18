<?php

namespace Modules\RoadmapManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\RoadmapManager\Models\Project;
use Modules\RoadmapManager\Models\ProjectGroup;

class RoadmapProjectController extends Controller{

    public function __construct( ) {
        $this->setIndexPage('roadmap', 'roadmap.index');
        $this->middleware('auth');
    }

    public function index(){

        $data = [ 
            'projects' => Project::with('roadmapGroups')->orderBy('updated_at', 'desc')->paginate(20) 
        ];
        return $this->view('roadmap-manager::projects.index', $data);
    }

    public function create(){

        $data = [
            'project' => new Project(),
            'groups' => ProjectGroup::orderBy('sort_order')->get(),
            'selectedGroups' => [],
        ];

        return $this->view('roadmap-manager::projects.form', $data);
    }

    public function store(Request $request)
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
            'slug' => $data['slug'] ?: \Illuminate\Support\Str::slug($data['name']),
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

        return redirect()->route('roadmap.projects.index')->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $project->load(['roadmapGroups', 'milestones', 'tasks']);
        return view('roadmap-manager::projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $groups = ProjectGroup::orderBy('sort_order')->get();
        $selectedGroups = $project->roadmapGroups()->pluck('wt_roadmap_groups.id')->toArray();

        return view('roadmap-manager::projects.form', compact('project', 'groups', 'selectedGroups'));
    }

    public function update(Request $request, Project $project)
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
            'slug' => $data['slug'] ?: \Illuminate\Support\Str::slug($data['name']),
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

        return redirect()->route('roadmap.projects.index')->with('success', 'Project updated successfully.');
    }
}
