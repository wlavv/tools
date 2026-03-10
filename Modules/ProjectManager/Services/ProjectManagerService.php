<?php

namespace Modules\ProjectManager\Services;

use Illuminate\Support\Str;
use Modules\ProjectManager\Models\ProjectManager;
use Modules\ProjectManager\Models\ProjectTask;

class ProjectManagerService
{
    public function getIndexData()
    {
        return ProjectManager::withCount([
                'tasks as tasks_total_count',
                'tasks as tasks_done_count' => fn ($query) => $query->where('status', 'Done'),
            ])
            ->with('children')
            ->orderBy('priority')
            ->orderBy('name')
            ->get()
            ->map(function (ProjectManager $project) {
                $project->progress_percent = $this->calculateProgress(
                    $project->tasks_total_count,
                    $project->tasks_done_count
                );
                return $project;
            });
    }

    public function getStats(): array
    {
        $projects = ProjectManager::count();
        $openProjects = ProjectManager::whereNotIn('status', ['Done', 'Cancelled'])->count();
        $tasks = ProjectTask::count();
        $tasksDone = ProjectTask::where('status', 'Done')->count();

        return [
            'projects' => $projects,
            'open_projects' => $openProjects,
            'tasks' => $tasks,
            'tasks_done' => $tasksDone,
        ];
    }

    public function createProject(array $data): ProjectManager
    {
        $data['slug'] = $this->normalizeSlug($data['slug'] ?? null, $data['name']);
        $data['id_parent'] = $data['id_parent'] ?? 0;
        $data['priority'] = $data['priority'] ?? 0;

        return ProjectManager::create($data);
    }

    public function updateProject(ProjectManager $project, array $data): ProjectManager
    {
        $data['slug'] = $this->normalizeSlug($data['slug'] ?? null, $data['name']);
        $data['id_parent'] = $data['id_parent'] ?? 0;
        $data['priority'] = $data['priority'] ?? 0;

        $project->update($data);

        return $project->refresh();
    }

    public function deleteProject(ProjectManager $project): void
    {
        $project->tasks()->delete();
        $project->delete();
    }

    public function getProjectDetail(ProjectManager $project): ProjectManager
    {
        $project->load([
            'children',
            'tasks',
            'rootTasks.children.children',
        ]);

        $total = $project->tasks()->count();
        $done = $project->tasks()->where('status', 'Done')->count();

        $project->progress_percent = $this->calculateProgress($total, $done);
        $project->tasks_total_count = $total;
        $project->tasks_done_count = $done;

        return $project;
    }

    public function createTask(ProjectManager $project, array $data): ProjectTask
    {
        $data['id_project'] = $project->id;
        $data['id_parent'] = $data['id_parent'] ?? 0;
        $data['priority'] = $data['priority'] ?? 0;

        return ProjectTask::create($data);
    }

    public function updateTask(ProjectManager $project, ProjectTask $task, array $data): ProjectTask
    {
        if ((int) $task->id_project !== (int) $project->id) {
            abort(404);
        }

        $task->update($data);

        return $task->refresh();
    }

    public function deleteTask(ProjectManager $project, ProjectTask $task): void
    {
        if ((int) $task->id_project !== (int) $project->id) {
            abort(404);
        }

        $this->deleteChildren($task);
        $task->delete();
    }

    public function toggleTask(ProjectManager $project, ProjectTask $task): ProjectTask
    {
        if ((int) $task->id_project !== (int) $project->id) {
            abort(404);
        }

        $task->status = $task->status === 'Done' ? 'Pending' : 'Done';
        $task->save();

        return $task->refresh();
    }

    protected function deleteChildren(ProjectTask $task): void
    {
        foreach ($task->children as $child) {
            $this->deleteChildren($child);
            $child->delete();
        }
    }

    protected function normalizeSlug(?string $slug, string $fallback): string
    {
        return Str::slug($slug ?: $fallback);
    }

    protected function calculateProgress(int $total, int $done): int
    {
        if ($total < 1) {
            return 0;
        }

        return (int) round(($done / $total) * 100);
    }
}
