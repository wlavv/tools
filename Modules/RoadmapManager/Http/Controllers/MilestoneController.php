<?php

namespace Modules\RoadmapManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\RoadmapManager\Models\Milestone;
use Modules\RoadmapManager\Models\Project;

class MilestoneController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(): View
    {
        $milestones = Milestone::with('project')->orderBy('planned_end_date')->paginate(20);
        return $this->view('roadmap-manager::milestones.index', compact('milestones'));
    }

    public function create(): View
    {
        return $this->view('roadmap-manager::milestones.form', [
            'milestone' => new Milestone(),
            'projects' => Project::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'project_id' => 'required|integer|exists:wt_projects,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'status' => 'required|in:planned,in_progress,completed,delayed,cancelled',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'is_critical' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['uuid'] = (string) Str::uuid();
        $data['created_by'] = auth()->id();

        Milestone::create($data);

        return redirect()->route('roadmap_manager.milestones.index')->with('success', 'Milestone created successfully.');
    }

    public function show(Milestone $milestone): View
    {
        $milestone->load(['project', 'tasks']);
        return $this->view('roadmap-manager::milestones.show', compact('milestone'));
    }

    public function edit(Milestone $milestone): View
    {
        return $this->view('roadmap-manager::milestones.form', [
            'milestone' => $milestone,
            'projects' => Project::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Milestone $milestone): RedirectResponse
    {
        $data = $request->validate([
            'project_id' => 'required|integer|exists:wt_projects,id',
            'name' => 'required|string|max:200',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'status' => 'required|in:planned,in_progress,completed,delayed,cancelled',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date',
            'progress_percentage' => 'nullable|integer|min:0|max:100',
            'is_critical' => 'nullable|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $milestone->update($data);

        return redirect()->route('roadmap_manager.milestones.index')->with('success', 'Milestone updated successfully.');
    }
}
