<?php
namespace Modules\ProjectManager\Services;

use Illuminate\Support\Str;
use Modules\ProjectManager\Models\ProjectManager;
use Modules\ProjectManager\Models\ProjectTask;

class ProjectManagerService
{
    public function getGroups()
    {
        return ProjectManager::where('id_parent',0)
            ->with(['children','tasks','children.tasks'])
            ->withCount(['children'])
            ->orderBy('priority')->orderBy('name')->get()
            ->map(function ($group) {
                $total = $group->tasks->count();
                $done = $group->tasks->where('status','Done')->count();
                foreach ($group->children as $child) {
                    $child->tasks_total_count = $child->tasks->count();
                    $child->tasks_done_count = $child->tasks->where('status','Done')->count();
                    $child->progress_percent = $child->tasks_total_count > 0 ? (int) round(($child->tasks_done_count / $child->tasks_total_count) * 100) : 0;
                    $total += $child->tasks_total_count;
                    $done += $child->tasks_done_count;
                }
                $group->aggregated_tasks_total = $total;
                $group->aggregated_tasks_done = $done;
                $group->progress_percent = $total > 0 ? (int) round(($done / $total) * 100) : 0;
                return $group;
            });
    }

    public function getStats(): array
    {
        return [
            'projects' => ProjectManager::count(),
            'root_projects' => ProjectManager::where('id_parent',0)->count(),
            'tasks' => ProjectTask::count(),
            'tasks_done' => ProjectTask::where('status','Done')->count(),
        ];
    }

    public function store(array $data): ProjectManager
    {
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
        $data['priority'] = $data['priority'] ?? 0;
        $data['id_parent'] = $data['id_parent'] ?? 0;
        return ProjectManager::create($data);
    }

    public function update(ProjectManager $project, array $data): ProjectManager
    {
        $data['slug'] = Str::slug($data['slug'] ?? $data['name']);
        $data['priority'] = $data['priority'] ?? 0;
        $data['id_parent'] = $data['id_parent'] ?? 0;
        $project->update($data);
        return $project->refresh();
    }
}
