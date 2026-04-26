<?php
namespace Modules\ProjectManager\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
                $done = $group->tasks->where('status', ProjectTask::STATUS_DONE)->count();
                foreach ($group->children as $child) {
                    $child->tasks_total_count = $child->tasks->count();
                    $child->tasks_done_count = $child->tasks->where('status', ProjectTask::STATUS_DONE)->count();
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
            'tasks_done' => ProjectTask::where('status', ProjectTask::STATUS_DONE)->count(),
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

    public function createTask(ProjectManager $project, array $data): ProjectTask
    {
        return DB::transaction(function () use ($project, $data) {
            $dependencyIds = Arr::pull($data, 'dependency_ids', []);

            $data['id_project'] = $project->id;
            $data['start_date'] = $data['start_date'] ?? now();
            $data['id_parent'] = $data['id_parent'] ?? 0;
            $data['priority'] = $data['priority'] ?? 3;
            $data['status'] = $data['status'] ?? ProjectTask::STATUS_PENDING;
            $data['execution_order'] = $data['execution_order'] ?? 0;
            $data['source'] = $data['source'] ?? 'manual';
            $data['completed_at'] = ((int) $data['status'] === ProjectTask::STATUS_DONE) ? now() : null;

            $task = ProjectTask::create($data);
            $this->syncTaskDependencies($task, $dependencyIds);

            return $task->refresh();
        });
    }

    public function updateTask(ProjectTask $task, array $data): ProjectTask
    {
        return DB::transaction(function () use ($task, $data) {
            $dependencyIds = Arr::pull($data, 'dependency_ids', []);

            if (array_key_exists('status', $data)) {
                $data['completed_at'] = ((int) $data['status'] === ProjectTask::STATUS_DONE)
                    ? ($task->completed_at ?? now())
                    : null;
            }

            $task->update($data);
            $this->syncTaskDependencies($task, $dependencyIds);

            return $task->refresh();
        });
    }

    public function deleteTask(ProjectTask $task): void
    {
        DB::transaction(function () use ($task) {
            DB::table('wt_todo_dependencies')
                ->where('task_id', $task->id)
                ->orWhere('depends_on_task_id', $task->id)
                ->delete();

            ProjectTask::where('id_parent', $task->id)->update(['id_parent' => 0]);
            $task->delete();
        });
    }

    public function completeTask(ProjectTask $task): ProjectTask
    {
        $task->update([
            'status' => ProjectTask::STATUS_DONE,
            'completed_at' => now(),
        ]);

        return $task->refresh();
    }

    public function reopenTask(ProjectTask $task): ProjectTask
    {
        $task->update([
            'status' => ProjectTask::STATUS_ACTIVE,
            'completed_at' => null,
        ]);

        return $task->refresh();
    }

    public function getProjectTaskOptions(ProjectManager $project, ?ProjectTask $exclude = null)
    {
        return $project->tasks()
            ->when($exclude, fn ($query) => $query->where('id', '!=', $exclude->id))
            ->orderBy('execution_order')
            ->orderBy('priority')
            ->orderBy('title')
            ->get();
    }

    public function getRecommendedTasks(int $limit = 10)
    {
        return ProjectTask::query()
            ->with(['project', 'dependencies'])
            ->executable()
            ->orderByRaw('CASE WHEN scheduled_for IS NULL THEN 1 ELSE 0 END')
            ->orderBy('scheduled_for')
            ->orderBy('priority')
            ->orderBy('execution_order')
            ->limit($limit)
            ->get();
    }

    protected function syncTaskDependencies(ProjectTask $task, array $dependencyIds): void
    {
        $dependencyIds = collect($dependencyIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== (int) $task->id)
            ->unique()
            ->values()
            ->all();

        $task->dependencies()->sync($dependencyIds);
    }
}
