<?php

namespace Modules\RoadmapManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\RoadmapManager\Models\ProjectGroup;

class RoadmapGroupController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): View
    {
        return $this->view('roadmap-manager::groups.index', [
            'groups' => ProjectGroup::withCount('projects')->orderBy('sort_order')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return $this->view('roadmap-manager::groups.form', ['group' => new ProjectGroup()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:150|unique:wt_roadmap_groups,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'status' => 'required|in:active,archived,planning',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        ProjectGroup::create($data);

        return redirect()->route('roadmap_manager.groups.index')->with('success', 'Group created successfully.');
    }

    public function show(ProjectGroup $group): View
    {
        $group->loadCount('projects');
        $group->load('projects');
        return $this->view('roadmap-manager::groups.show', compact('group'));
    }

    public function edit(ProjectGroup $group): View
    {
        return $this->view('roadmap-manager::groups.form', compact('group'));
    }

    public function update(Request $request, ProjectGroup $group): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:150|unique:wt_roadmap_groups,slug,' . $group->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'status' => 'required|in:active,archived,planning',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $group->update($data);

        return redirect()->route('roadmap_manager.groups.index')->with('success', 'Group updated successfully.');
    }
}
