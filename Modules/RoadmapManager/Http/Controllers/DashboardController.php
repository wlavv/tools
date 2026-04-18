<?php

namespace Modules\RoadmapManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Modules\RoadmapManager\Models\Project;
use Modules\RoadmapManager\Models\ProjectGroup;
use Modules\RoadmapManager\Models\Milestone;
use Modules\RoadmapManager\Models\TaskItem;

class DashboardController extends Controller{

    public function __construct( ) {
        $this->setIndexPage('roadmap', 'roadmap.dashboard');
        $this->middleware('auth');
    }

    public function index(){
        
        $data = [
            'projectCount' => Project::count(),
            'groupCount' => ProjectGroup::count(),
            'milestoneCount' => Milestone::count(),
            'taskCount' => TaskItem::count(),
            'taskStatus' => TaskItem::select('status', DB::raw('COUNT(*) as total'))->groupBy('status')->pluck('total', 'status')->toArray(),
            'groupProjects' => ProjectGroup::withCount('projects')->orderBy('sort_order')->get(),
            'recentProjects' => Project::latest('updated_at')->limit(8)->get(),
            'upcomingMilestones' => Milestone::with('project')->whereNotNull('planned_end_date')->orderBy('planned_end_date')->limit(8)->get(),
        ];

        return $this->view('roadmap-manager::dashboard.index', $data);
    }
}
