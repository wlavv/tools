<?php

namespace Modules\ProjectManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\ProjectManager\Services\ProjectManagerSectionRegistry;

class DashboardController extends Controller

{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    private array $openProjectStatuses = ['active', 'in_progress', 'in progress', 'execution', 'running', 'open'];
    private array $openTaskStatuses = ['new', 'todo', 'open', 'pending', 'ready', 'in_progress', 'in progress', 'waiting', 'blocked', 'review'];
    private array $closedStatuses = ['done', 'completed', 'cancelled', 'archived', 'closed'];

    public function index(Request $request)
    {
        $selectedProjectId = $request->integer('project') ?: null;

        $allProjects = Schema::hasTable('wt_projects')
            ? DB::table('wt_projects')
                ->when(Schema::hasColumn('wt_projects', 'is_pinned'), fn ($q) => $q->orderByDesc('is_pinned'))
                ->orderByRaw("FIELD(status, 'in_progress', 'in progress', 'active', 'execution', 'running', 'open', 'hold', 'on_hold', 'paused', 'blocked', 'pending', 'planning', 'planned', 'done', 'completed', 'closed', 'archived')")
                ->orderBy('name')
                ->get()
            : collect();

        $allProjects = $this->attachProjectLogos($allProjects);
        $projects = $allProjects;

        $projectGroups = [
            'execution' => collect(),
            'hold' => collect(),
            'pending' => collect(),
            'done' => collect(),
        ];

        foreach ($allProjects as $project) {
            $projectGroups[$this->projectStatusGroup($project->status ?? null)]->push($project);
        }

        $openProjects = $projectGroups['execution'];
        $projectIdsForExecution = $openProjects->pluck('id')->map(fn ($id) => (int) $id)->all();

        $activeMilestones = [];
        $activeMilestoneIds = [];
        foreach ($openProjects as $project) {
            $milestone = $this->activeMilestone((int) $project->id);
            if ($milestone) {
                $milestone->project_name = $project->name ?? ('Projeto #' . $project->id);
                $milestone->project_id = (int) $project->id;
                $activeMilestones[(int) $project->id] = $milestone;
                $activeMilestoneIds[] = (int) $milestone->id;
            }
        }

        $this->ensureMatrixDefaultsForMilestones($activeMilestoneIds);

        $matrixTasks = $this->tasksForMilestones($activeMilestoneIds, $this->openTaskStatuses, 500, true);
        $ganttTasks = $this->tasksForMilestones($activeMilestoneIds, $this->openTaskStatuses, 250);
        $executionCounters = $this->executionCounters($matrixTasks, $ganttTasks, $activeMilestones);
        $milestoneCards = collect($activeMilestones)->values();

        $stats = [
            'projects_execution' => $projectGroups['execution']->count(),
            'projects_hold' => $projectGroups['hold']->count(),
            'projects_pending' => $projectGroups['pending']->count(),
            'projects_done' => $projectGroups['done']->count(),
            'active_milestones' => count($activeMilestones),
            'matrix_tasks' => $matrixTasks->count(),
            'gantt_tasks' => $ganttTasks->count(),
            'blocked' => $this->safeCount('wt_project_tasks', ['status' => 'blocked']),
        ];

        $quickProjects = $allProjects->whereNotIn('status', ['done', 'completed', 'closed', 'archived'])->values();
        $quickMilestones = $this->milestonesForProjects($quickProjects->pluck('id')->map(fn ($id) => (int) $id)->all());
        $quickParentTasks = $this->parentTasksForProjects($quickProjects->pluck('id')->map(fn ($id) => (int) $id)->all());

        return $this->view('project-manager::dashboard.index', compact('projects', 'projectGroups', 'activeMilestones', 'milestoneCards', 'matrixTasks', 'ganttTasks', 'executionCounters', 'stats', 'selectedProjectId', 'quickProjects', 'quickMilestones', 'quickParentTasks'));
    }

    public function productivity()
    {
        $projects = $this->openProjects();
        $activeMilestones = [];
        $activeMilestoneIds = [];

        foreach ($projects as $project) {
            $milestone = $this->activeMilestone((int) $project->id);
            if ($milestone) {
                $milestone->project_name = $project->name ?? ('Projeto #' . $project->id);
                $milestone->project_id = (int) $project->id;
                $activeMilestones[(int) $project->id] = $milestone;
                $activeMilestoneIds[] = (int) $milestone->id;
            }
        }

        $this->ensureMatrixDefaultsForMilestones($activeMilestoneIds);

        $executionTasks = $this->tasksForMilestones($activeMilestoneIds, ['in_progress', 'review'], 200);
        $nextTasks = $this->tasksForMilestones($activeMilestoneIds, ['pending', 'ready', 'waiting'], 200);
        $blockedTasks = $this->tasksForMilestones($activeMilestoneIds, ['blocked'], 200);
        $matrixTasks = $this->tasksForMilestones($activeMilestoneIds, $this->openTaskStatuses, 300, true);
        $ganttTasks = $this->tasksForMilestones($activeMilestoneIds, $this->openTaskStatuses, 120);
        $dependencies = $this->globalDependencies($projects->pluck('id')->map(fn ($id) => (int) $id)->all());

        return $this->view('project-manager::dashboard.productivity', compact('projects', 'activeMilestones', 'executionTasks', 'nextTasks', 'blockedTasks', 'matrixTasks', 'ganttTasks', 'dependencies'));
    }

    public function moveGlobalTaskPanel(Request $request, int $task)
    {
        abort_unless(Schema::hasTable('wt_project_tasks'), 404);

        $data = $request->validate([
            'panel' => ['required', 'string', 'in:next,execution,review,done'],
        ]);

        $statusMap = [
            'next' => 'ready',
            'execution' => 'in_progress',
            'review' => 'review',
            'done' => 'completed',
        ];

        $record = DB::table('wt_project_tasks')->where('id', $task)->first();
        abort_unless($record, 404);

        $update = ['status' => $statusMap[$data['panel']]];
        if ($data['panel'] === 'execution' && Schema::hasColumn('wt_project_tasks', 'started_at')) {
            $update['started_at'] = now();
        }
        if ($data['panel'] === 'done' && Schema::hasColumn('wt_project_tasks', 'completed_at')) {
            $update['completed_at'] = now();
        }
        if (Schema::hasColumn('wt_project_tasks', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table('wt_project_tasks')->where('id', $task)->update($update);

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'task_id' => $task, 'status' => $statusMap[$data['panel']]])
            : redirect()->back()->with('success', 'Task movida com sucesso.');
    }

    public function updateGlobalTaskMatrix(Request $request, int $task)
    {
        abort_unless(Schema::hasTable('wt_project_tasks'), 404);

        $data = $request->validate([
            'importance' => ['required', 'integer', 'min:1', 'max:5'],
            'urgency' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        abort_unless(DB::table('wt_project_tasks')->where('id', $task)->exists(), 404);

        $update = [];
        if (Schema::hasColumn('wt_project_tasks', 'importance')) {
            $update['importance'] = (int) $data['importance'];
        }
        if (Schema::hasColumn('wt_project_tasks', 'urgency')) {
            $update['urgency'] = (int) $data['urgency'];
        }
        if (Schema::hasColumn('wt_project_tasks', 'priority_score')) {
            $update['priority_score'] = ((int) $data['importance'] * 2) + (int) $data['urgency'];
        }
        if (Schema::hasColumn('wt_project_tasks', 'updated_at')) {
            $update['updated_at'] = now();
        }

        if ($update) {
            DB::table('wt_project_tasks')->where('id', $task)->update($update);
        }

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'task_id' => $task, 'importance' => (int) $data['importance'], 'urgency' => (int) $data['urgency']])
            : redirect()->back()->with('success', 'Prioridade atualizada.');
    }

    public function blockGlobalTask(Request $request, int $task)
    {
        abort_unless(Schema::hasTable('wt_project_tasks'), 404);

        $data = $request->validate([
            'block_type' => ['required', 'string', 'max:80'],
            'blocked_reason' => ['required', 'string', 'max:2000'],
            'dependency_id' => ['nullable', 'integer'],
        ]);

        $record = DB::table('wt_project_tasks')->where('id', $task)->first();
        abort_unless($record, 404);

        $update = ['status' => 'blocked'];
        if (Schema::hasColumn('wt_project_tasks', 'blocked_reason')) {
            $update['blocked_reason'] = $data['blocked_reason'];
        }
        if (Schema::hasColumn('wt_project_tasks', 'block_type')) {
            $update['block_type'] = $data['block_type'];
        }
        if (Schema::hasColumn('wt_project_tasks', 'blocked_at')) {
            $update['blocked_at'] = now();
        }
        if (Schema::hasColumn('wt_project_tasks', 'updated_at')) {
            $update['updated_at'] = now();
        }
        DB::table('wt_project_tasks')->where('id', $task)->update($update);

        return redirect()->back()->with('success', 'Task movida para bloqueio.');
    }


    public function updateProjectStatus(Request $request, int $project)
    {
        abort_unless(Schema::hasTable('wt_projects'), 404);

        $data = $request->validate([
            'status' => ['required', 'string', 'in:in_progress,hold,pending,done'],
        ]);

        abort_unless(DB::table('wt_projects')->where('id', $project)->exists(), 404);

        $update = ['status' => $data['status']];
        if (Schema::hasColumn('wt_projects', 'updated_at')) {
            $update['updated_at'] = now();
        }

        DB::table('wt_projects')->where('id', $project)->update($update);

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'project_id' => $project, 'status' => $data['status']])
            : redirect()->back()->with('success', 'Estado do projeto atualizado.');
    }

    public function storeQuickTask(Request $request)
    {
        abort_unless(Schema::hasTable('wt_project_tasks'), 404);

        $data = $request->validate([
            'project_id' => ['required', 'integer'],
            'milestone_id' => ['required', 'integer'],
            'parent_task_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['nullable', 'string', 'max:40'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
            'expected_time' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'due_date' => ['nullable', 'date'],
            'importance' => ['nullable', 'integer', 'min:1', 'max:5'],
            'urgency' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $projectColumn = $this->projectColumn('wt_project_tasks');
        $parentColumn = $this->parentColumn('wt_project_tasks');
        $parentId = !empty($data['parent_task_id']) ? (int) $data['parent_task_id'] : (int) $data['milestone_id'];

        $projectExists = Schema::hasTable('wt_projects') && DB::table('wt_projects')->where('id', (int) $data['project_id'])->exists();
        abort_unless($projectExists, 422, 'Projeto inválido.');

        $milestoneValid = DB::table('wt_project_tasks')
            ->where('id', (int) $data['milestone_id'])
            ->where($projectColumn, (int) $data['project_id'])
            ->exists();
        abort_unless($milestoneValid, 422, 'Milestone inválido para o projeto selecionado.');

        if (!empty($data['parent_task_id'])) {
            $parentValid = DB::table('wt_project_tasks')
                ->where('id', (int) $data['parent_task_id'])
                ->where($projectColumn, (int) $data['project_id'])
                ->exists();
            abort_unless($parentValid, 422, 'Task pai inválida para o projeto selecionado.');
        }

        $importance = (int)($data['importance'] ?? 3);
        $urgency = (int)($data['urgency'] ?? 3);

        $insert = [];
        $this->setIfColumn($insert, 'wt_project_tasks', $projectColumn, (int) $data['project_id']);
        $this->setIfColumn($insert, 'wt_project_tasks', $parentColumn, $parentId);
        $this->setIfColumn($insert, 'wt_project_tasks', 'title', $data['title']);
        $this->setIfColumn($insert, 'wt_project_tasks', 'description', $data['description'] ?? null);
        $this->setIfColumn($insert, 'wt_project_tasks', 'comment', $data['description'] ?? null);
        $this->setIfColumn($insert, 'wt_project_tasks', 'type', 'task');
        $this->setIfColumn($insert, 'wt_project_tasks', 'status', $data['status'] ?? 'ready');
        $this->setIfColumn($insert, 'wt_project_tasks', 'priority', (int)($data['priority'] ?? 5));
        $this->setIfColumn($insert, 'wt_project_tasks', 'expected_time', $data['expected_time'] ?? null);
        $this->setIfColumn($insert, 'wt_project_tasks', 'due_date', $data['due_date'] ?? null);
        $this->setIfColumn($insert, 'wt_project_tasks', 'importance', $importance);
        $this->setIfColumn($insert, 'wt_project_tasks', 'urgency', $urgency);
        $this->setIfColumn($insert, 'wt_project_tasks', 'priority_score', ($importance * 2) + $urgency);
        $this->setIfColumn($insert, 'wt_project_tasks', 'execution_order', $this->nextExecutionOrder('wt_project_tasks', $projectColumn, (int) $data['project_id'], $parentColumn, $parentId));
        $this->setIfColumn($insert, 'wt_project_tasks', 'created_by', auth()->id());
        $this->setIfColumn($insert, 'wt_project_tasks', 'assigned_to', auth()->id());
        $this->setIfColumn($insert, 'wt_project_tasks', 'created_at', now());
        $this->setIfColumn($insert, 'wt_project_tasks', 'updated_at', now());

        $taskId = DB::table('wt_project_tasks')->insertGetId($insert);

        return $request->expectsJson()
            ? response()->json(['ok' => true, 'task_id' => $taskId])
            : redirect()->route('project_manager.index')->with('success', 'Task criada com sucesso.');
    }

    private function attachProjectLogos($projects)
    {
        if ($projects->isEmpty()) {
            return $projects;
        }

        $projectIds = $projects->pluck('id')->map(fn ($id) => (int) $id)->filter()->values()->all();
        $assetLogos = collect();

        if (Schema::hasTable('wt_project_assets') && Schema::hasColumn('wt_project_assets', 'public_url') && !empty($projectIds)) {
            $assetProjectColumn = $this->projectColumn('wt_project_assets');

            $query = DB::table('wt_project_assets')
                ->whereIn($assetProjectColumn, $projectIds)
                ->whereNotNull('public_url')
                ->where('public_url', '<>', '');

            if (Schema::hasColumn('wt_project_assets', 'type')) {
                $query->whereIn('type', ['logo', 'icon', 'image']);
            }

            $logoRows = $query
                ->when(Schema::hasColumn('wt_project_assets', 'is_primary'), fn ($q) => $q->orderByDesc('is_primary'))
                ->when(Schema::hasColumn('wt_project_assets', 'execution_order'), fn ($q) => $q->orderBy('execution_order'))
                ->orderBy('id')
                ->get();

            foreach ($logoRows as $row) {
                $pid = (int) ($row->{$assetProjectColumn} ?? 0);
                if ($pid && !$assetLogos->has($pid)) {
                    $assetLogos->put($pid, $row->public_url);
                }
            }
        }

        return $projects->map(function ($project) use ($assetLogos) {
            $projectId = (int) ($project->id ?? 0);
            $projectLogo = null;

            foreach (['logo', 'logo_url', 'image', 'icon'] as $column) {
                if (property_exists($project, $column) && !empty($project->{$column})) {
                    $projectLogo = $project->{$column};
                    break;
                }
            }

            $project->project_logo_url = $projectLogo ?: ($assetLogos[$projectId] ?? null);
            $project->project_initials = $this->projectInitials($project->name ?? ('P' . $projectId));

            return $project;
        });
    }

    private function projectInitials(?string $name): string
    {
        $name = trim((string) $name);
        if ($name === '') {
            return 'P';
        }

        $words = preg_split('/\s+/', $name) ?: [];
        $initials = '';

        foreach ($words as $word) {
            $clean = trim($word);
            if ($clean === '') {
                continue;
            }
            $initials .= mb_strtoupper(mb_substr($clean, 0, 1));
            if (mb_strlen($initials) >= 2) {
                break;
            }
        }

        return $initials ?: mb_strtoupper(mb_substr($name, 0, 2));
    }

    private function openProjects()
    {
        if (!Schema::hasTable('wt_projects')) {
            return collect();
        }

        return DB::table('wt_projects')
            ->whereIn('status', $this->openProjectStatuses)
            ->orderByDesc('is_pinned')
            ->orderBy('name')
            ->get();
    }

    private function tasksForMilestones(array $milestoneIds, array $statuses, int $limit = 200, bool $matrixSort = false)
    {
        if (!Schema::hasTable('wt_project_tasks') || !Schema::hasColumn('wt_project_tasks', 'parent_id') || empty($milestoneIds)) {
            return collect();
        }

        $projectColumn = $this->projectColumn('wt_project_tasks');

        $query = DB::table('wt_project_tasks as t')
            ->leftJoin('wt_projects as p', 'p.id', '=', 't.' . $projectColumn)
            ->leftJoin('wt_project_tasks as m', 'm.id', '=', 't.parent_id')
            ->select('t.*', DB::raw('t.' . $projectColumn . ' as project_id'), 'p.name as project_name', 'm.title as milestone_title')
            ->whereIn('t.parent_id', $milestoneIds);

        if (Schema::hasColumn('wt_project_tasks', 'type')) {
            $query->where(function ($q) {
                $q->whereNull('t.type')->orWhereNotIn('t.type', ['milestone', 'phase']);
            });
        }

        if (Schema::hasColumn('wt_project_tasks', 'status')) {
            if (!empty($statuses)) {
                $query->whereIn('t.status', $statuses);
            }
        }

        if ($matrixSort && Schema::hasColumn('wt_project_tasks', 'priority_score')) {
            $query->orderByDesc('t.priority_score');
        }
        if ($matrixSort && Schema::hasColumn('wt_project_tasks', 'importance')) {
            $query->orderByDesc('t.importance');
        }
        if ($matrixSort && Schema::hasColumn('wt_project_tasks', 'urgency')) {
            $query->orderByDesc('t.urgency');
        }

        return $query
            ->when(Schema::hasColumn('wt_project_tasks', 'status'), fn ($q) => $q->orderByRaw("FIELD(t.status, 'in_progress', 'in progress', 'review', 'ready', 'open', 'todo', 'new', 'waiting', 'pending', 'blocked')"))
            ->when(Schema::hasColumn('wt_project_tasks', 'priority'), fn ($q) => $q->orderBy('t.priority'))
            ->when(Schema::hasColumn('wt_project_tasks', 'execution_order'), fn ($q) => $q->orderBy('t.execution_order'))
            ->orderBy('t.id')
            ->limit($limit)
            ->get();
    }

    private function ensureMatrixDefaultsForMilestones(array $milestoneIds): void
    {
        if (!Schema::hasTable('wt_project_tasks') || !Schema::hasColumn('wt_project_tasks', 'parent_id') || empty($milestoneIds)) {
            return;
        }

        $hasImportance = Schema::hasColumn('wt_project_tasks', 'importance');
        $hasUrgency = Schema::hasColumn('wt_project_tasks', 'urgency');
        $hasScore = Schema::hasColumn('wt_project_tasks', 'priority_score');
        if (!$hasImportance && !$hasUrgency && !$hasScore) {
            return;
        }

        $tasks = DB::table('wt_project_tasks')
            ->whereIn('parent_id', $milestoneIds)
            ->when(Schema::hasColumn('wt_project_tasks', 'status'), fn ($q) => $q->whereNotIn('status', $this->closedStatuses))
            ->limit(500)
            ->get();

        foreach ($tasks as $task) {
            $importance = $hasImportance ? ($task->importance ?? null) : null;
            $urgency = $hasUrgency ? ($task->urgency ?? null) : null;
            if ($importance !== null && $urgency !== null && (!$hasScore || $task->priority_score !== null)) {
                continue;
            }

            $defaultImportance = $importance ?? $this->defaultImportance($task);
            $defaultUrgency = $urgency ?? $this->defaultUrgency($task);
            $update = [];
            if ($hasImportance && $importance === null) {
                $update['importance'] = $defaultImportance;
            }
            if ($hasUrgency && $urgency === null) {
                $update['urgency'] = $defaultUrgency;
            }
            if ($hasScore && ($task->priority_score ?? null) === null) {
                $update['priority_score'] = ((int) $defaultImportance * 2) + (int) $defaultUrgency;
            }
            if ($update && Schema::hasColumn('wt_project_tasks', 'updated_at')) {
                $update['updated_at'] = now();
            }
            if ($update) {
                DB::table('wt_project_tasks')->where('id', $task->id)->update($update);
            }
        }
    }

    private function globalDependencies(array $projectIds)
    {
        if (!Schema::hasTable('wt_project_task_dependencies') || empty($projectIds)) {
            return collect();
        }

        return DB::table('wt_project_task_dependencies')
            ->whereIn($this->projectColumn('wt_project_task_dependencies'), $projectIds)
            ->when(Schema::hasColumn('wt_project_task_dependencies', 'status'), fn ($q) => $q->where('status', 'active'))
            ->limit(200)
            ->get();
    }

    private function activeMilestone(int $projectId)
    {
        if (!Schema::hasTable('wt_project_tasks')) {
            return null;
        }

        $query = DB::table('wt_project_tasks')->where($this->projectColumn('wt_project_tasks'), $projectId);
        Schema::hasColumn('wt_project_tasks', 'type') ? $query->where('type', 'milestone') : $query->where('parent_id', 0);
        if (Schema::hasColumn('wt_project_tasks', 'status')) {
            $query->whereNotIn('status', $this->closedStatuses);
            $query->orderByRaw("FIELD(status, 'in_progress', 'in progress', 'active', 'execution', 'running', 'ready', 'open', 'todo', 'new', 'pending', 'waiting', 'blocked', 'review')");
        }

        return $query
            ->when(Schema::hasColumn('wt_project_tasks', 'execution_order'), fn ($q) => $q->orderBy('execution_order'))
            ->orderBy('id')
            ->first();
    }

    private function nextMilestones(int $projectId, ?int $activeMilestoneId, int $limit = 2)
    {
        if (!Schema::hasTable('wt_project_tasks')) {
            return collect();
        }

        $query = DB::table('wt_project_tasks')->where($this->projectColumn('wt_project_tasks'), $projectId);
        Schema::hasColumn('wt_project_tasks', 'type') ? $query->where('type', 'milestone') : $query->where('parent_id', 0);
        if (Schema::hasColumn('wt_project_tasks', 'status')) {
            $query->whereNotIn('status', $this->closedStatuses);
        }
        $milestones = $query->when(Schema::hasColumn('wt_project_tasks', 'execution_order'), fn ($q) => $q->orderBy('execution_order'))->orderBy('id')->get()->values();

        if (!$activeMilestoneId) {
            return $milestones->take($limit);
        }
        $index = $milestones->search(fn ($item) => (int) $item->id === $activeMilestoneId);
        return $index === false ? $milestones->take($limit) : $milestones->slice($index + 1, $limit)->values();
    }

    private function childrenOfMilestone(int $projectId, int $milestoneId, int $limit = 6)
    {
        if (!Schema::hasTable('wt_project_tasks') || !Schema::hasColumn('wt_project_tasks', 'parent_id')) {
            return collect();
        }

        return DB::table('wt_project_tasks')
            ->where($this->projectColumn('wt_project_tasks'), $projectId)
            ->where('parent_id', $milestoneId)
            ->when(Schema::hasColumn('wt_project_tasks', 'status'), fn ($q) => $q->whereNotIn('status', $this->closedStatuses))
            ->when(Schema::hasColumn('wt_project_tasks', 'priority'), fn ($q) => $q->orderBy('priority'))
            ->when(Schema::hasColumn('wt_project_tasks', 'execution_order'), fn ($q) => $q->orderBy('execution_order'))
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    private function defaultImportance(object $task): int
    {
        $priority = (int)($task->priority ?? 3);
        if ($priority <= 2) {
            return 5;
        }
        if ($priority <= 4) {
            return 4;
        }
        return 3;
    }

    private function defaultUrgency(object $task): int
    {
        $status = (string)($task->status ?? '');
        if (in_array($status, ['in_progress', 'in progress', 'review'], true)) {
            return 5;
        }
        if (in_array($status, ['ready', 'waiting'], true)) {
            return 3;
        }
        return 2;
    }

    private function projectStatusGroup(?string $status): string
    {
        $status = strtolower((string) $status);

        if (in_array($status, ['in_progress', 'in progress', 'active', 'execution', 'running', 'open'], true)) {
            return 'execution';
        }
        if (in_array($status, ['hold', 'on_hold', 'paused', 'blocked'], true)) {
            return 'hold';
        }
        if (in_array($status, ['done', 'completed', 'closed', 'archived'], true)) {
            return 'done';
        }

        return 'pending';
    }

    private function executionCounters($matrixTasks, $ganttTasks, array $activeMilestones): array
    {
        $byStatus = [];
        foreach ($matrixTasks->merge($ganttTasks)->unique('id') as $task) {
            $status = (string)($task->status ?? 'pending');
            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
        }

        $byProject = [];
        foreach ($matrixTasks->merge($ganttTasks)->unique('id') as $task) {
            $projectId = (int)($task->project_id ?? 0);
            if (!$projectId) {
                continue;
            }
            if (!isset($byProject[$projectId])) {
                $byProject[$projectId] = [
                    'project_id' => $projectId,
                    'project_name' => $task->project_name ?? ('Projeto #' . $projectId),
                    'total' => 0,
                    'done' => 0,
                    'in_progress' => 0,
                    'blocked' => 0,
                ];
            }
            $byProject[$projectId]['total']++;
            $status = (string)($task->status ?? 'pending');
            if (in_array($status, ['done', 'completed'], true)) {
                $byProject[$projectId]['done']++;
            }
            if (in_array($status, ['in_progress', 'in progress', 'review'], true)) {
                $byProject[$projectId]['in_progress']++;
            }
            if ($status === 'blocked') {
                $byProject[$projectId]['blocked']++;
            }
        }

        return [
            'by_status' => $byStatus,
            'by_project' => array_values($byProject),
            'active_milestones' => count($activeMilestones),
        ];
    }

    private function safeCount(string $table, array $where = []): int
    {
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return 0;
        }

        $query = DB::table($table);
        foreach ($where as $column => $value) {
            if (DB::getSchemaBuilder()->hasColumn($table, $column)) {
                $query->where($column, $value);
            }
        }

        return (int) $query->count();
    }


    private function milestonesForProjects(array $projectIds)
    {
        if (!Schema::hasTable('wt_project_tasks') || empty($projectIds)) {
            return collect();
        }

        $projectColumn = $this->projectColumn('wt_project_tasks');
        $parentColumn = $this->parentColumn('wt_project_tasks');

        return DB::table('wt_project_tasks')
            ->select('id', DB::raw($projectColumn . ' as project_id'), 'title', 'status')
            ->whereIn($projectColumn, $projectIds)
            ->when(Schema::hasColumn('wt_project_tasks', 'type'), fn ($q) => $q->where('type', 'milestone'), fn ($q) => $q->where($parentColumn, 0))
            ->when(Schema::hasColumn('wt_project_tasks', 'status'), fn ($q) => $q->whereNotIn('status', $this->closedStatuses))
            ->when(Schema::hasColumn('wt_project_tasks', 'execution_order'), fn ($q) => $q->orderBy('execution_order'))
            ->orderBy('id')
            ->get()
            ->groupBy('project_id');
    }

    private function parentTasksForProjects(array $projectIds)
    {
        if (!Schema::hasTable('wt_project_tasks') || empty($projectIds)) {
            return collect();
        }

        $projectColumn = $this->projectColumn('wt_project_tasks');
        $parentColumn = $this->parentColumn('wt_project_tasks');

        return DB::table('wt_project_tasks as t')
            ->leftJoin('wt_project_tasks as m', 'm.id', '=', 't.' . $parentColumn)
            ->select('t.id', DB::raw('t.' . $projectColumn . ' as project_id'), DB::raw('t.' . $parentColumn . ' as parent_id'), 't.title', 'm.title as milestone_title')
            ->whereIn('t.' . $projectColumn, $projectIds)
            ->when(Schema::hasColumn('wt_project_tasks', 'type'), fn ($q) => $q->where(function ($sub) { $sub->whereNull('t.type')->orWhereNotIn('t.type', ['milestone', 'phase']); }))
            ->when(Schema::hasColumn('wt_project_tasks', 'status'), fn ($q) => $q->whereNotIn('t.status', $this->closedStatuses))
            ->orderBy('t.id')
            ->limit(500)
            ->get()
            ->groupBy('project_id');
    }

    private function parentColumn(string $table): string
    {
        return Schema::hasColumn($table, 'parent_id') ? 'parent_id' : 'id_parent';
    }

    private function nextExecutionOrder(string $table, string $projectColumn, int $projectId, ?string $parentColumn = null, ?int $parentId = null): int
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'execution_order')) {
            return 1;
        }

        $query = DB::table($table)->where($projectColumn, $projectId);
        if ($parentColumn && Schema::hasColumn($table, $parentColumn) && $parentId !== null) {
            $query->where($parentColumn, $parentId);
        }

        return ((int) $query->max('execution_order')) + 1;
    }

    private function setIfColumn(array &$payload, string $table, string $column, $value): void
    {
        if (Schema::hasColumn($table, $column)) {
            $payload[$column] = $value;
        }
    }

    private function projectColumn(string $table): string
    {
        return Schema::hasColumn($table, 'project_id') ? 'project_id' : 'id_project';
    }
}
