<?php

namespace Modules\ProjectManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\ProjectManager\Models\Project;
use Modules\ProjectManager\Services\ProjectManagerSectionRegistry;

class ProjectController extends Controller

{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    private array $openStatuses = ['pending', 'ready', 'in_progress', 'waiting', 'blocked', 'review'];
    private array $closedStatuses = ['done', 'completed', 'cancelled', 'archived'];

    public function index(Request $request)
    {
        $query = Project::query();

        if ($request->filled('search')) {
            $search = '%' . $request->get('search') . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('slug', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $projects = $query->orderByDesc('is_pinned')->orderBy('name')->get();

        return $this->view('project-manager::projects.index', compact('projects'));
    }

    public function create()
    {
        $project = new Project();
        return $this->view('project-manager::projects.form', compact('project'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['created_at'] = now();
        $data['updated_at'] = now();

        $project = Project::create($this->filterColumns('wt_projects', $data));

        return redirect()->route('project_manager.projects.show', $project->id)->with('success', 'Projeto criado com sucesso.');
    }

    public function show(Project $project)
    {
        $activeMilestone = $this->activeMilestone($project->id);
        $activeTasks = $activeMilestone ? $this->childrenOfMilestone($project->id, (int) $activeMilestone->id, 50) : collect();
        $activeMilestoneProgress = $activeMilestone ? $this->milestoneProgress($project->id, (int) $activeMilestone->id) : null;
        $nextMilestones = $this->nextMilestones($project->id, $activeMilestone ? (int) $activeMilestone->id : null, 2);
        $recentDecisions = $this->projectRecords('wt_project_decisions', $project->id, 4);
        $assetRecords = $this->projectRecords('wt_project_assets', $project->id, 20);
        $primaryLogo = $assetRecords->first(function ($asset) {
            return in_array(($asset->type ?? ''), ['logo', 'icon', 'image'], true) && !empty($asset->public_url) && (int)($asset->is_primary ?? 0) === 1;
        }) ?: $assetRecords->first(function ($asset) {
            return in_array(($asset->type ?? ''), ['logo', 'icon', 'image'], true) && !empty($asset->public_url);
        });

        return $this->view('project-manager::projects.show', compact('project', 'activeMilestone', 'activeTasks', 'activeMilestoneProgress', 'nextMilestones', 'recentDecisions', 'primaryLogo'));
    }

    public function tasks(Project $project)
    {
        $milestones = $this->allMilestones($project->id, true);
        return $this->view('project-manager::projects.tasks', compact('project', 'milestones'));
    }

    public function roadmap(Project $project)
    {
        $milestones = $this->allMilestones($project->id, true);
        $activeMilestone = $this->activeMilestone($project->id);
        $milestoneProgress = $milestones->mapWithKeys(function ($milestone) use ($project) {
            return [(int) $milestone->id => $this->milestoneProgress($project->id, (int) $milestone->id)];
        });

        return $this->view('project-manager::projects.roadmap', compact('project', 'milestones', 'activeMilestone', 'milestoneProgress'));
    }

    public function productivity(Project $project)
    {
        $activeMilestone = $this->activeMilestone($project->id);

        $executionTasks = $this->projectRecords('wt_project_tasks', $project->id, 30, function ($query) {
            if (Schema::hasColumn('wt_project_tasks', 'status')) {
                $query->whereIn('status', ['in_progress', 'review']);
                $query->orderByRaw("FIELD(status, 'in_progress', 'review')");
            }
            if (Schema::hasColumn('wt_project_tasks', 'priority_score')) {
                $query->orderByDesc('priority_score');
            }
            if (Schema::hasColumn('wt_project_tasks', 'importance')) {
                $query->orderByDesc('importance');
            }
            if (Schema::hasColumn('wt_project_tasks', 'urgency')) {
                $query->orderByDesc('urgency');
            }
            if (Schema::hasColumn('wt_project_tasks', 'execution_order')) {
                $query->orderBy('execution_order');
            }
        });

        $nextTasks = $activeMilestone ? $this->childrenOfMilestone($project->id, (int) $activeMilestone->id, 30, ['pending', 'ready', 'waiting']) : collect();

        $blockedTasks = $this->projectRecords('wt_project_tasks', $project->id, 30, function ($query) {
            if (Schema::hasColumn('wt_project_tasks', 'status')) {
                $query->where('status', 'blocked');
            }
            if (Schema::hasColumn('wt_project_tasks', 'updated_at')) {
                $query->orderByDesc('updated_at');
            }
        });

        $dependencies = $this->projectRecords('wt_project_task_dependencies', $project->id, 80, function ($query) {
            if (Schema::hasColumn('wt_project_task_dependencies', 'status')) {
                $query->where('status', 'active');
            }
        });

        $this->ensureMatrixDefaults($project->id);
        $matrixTasks = $this->matrixTasks($project->id);

        $milestones = $this->allMilestones($project->id, true);
        $ganttTasks = $this->projectRecords('wt_project_tasks', $project->id, 80, function ($query) {
            if (Schema::hasColumn('wt_project_tasks', 'type')) {
                $query->where('type', '<>', 'milestone');
            }
            if (Schema::hasColumn('wt_project_tasks', 'start_date')) {
                $query->orderBy('start_date');
            } else {
                $query->orderBy('id');
            }
        });

        return $this->view('project-manager::projects.productivity', compact('project', 'activeMilestone', 'executionTasks', 'nextTasks', 'blockedTasks', 'dependencies', 'milestones', 'ganttTasks', 'matrixTasks'));
    }

    public function moveTaskPanel(Request $request, Project $project, int $task)
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

        $record = DB::table('wt_project_tasks')
            ->where('id', $task)
            ->where($this->projectColumn('wt_project_tasks'), $project->id)
            ->first();
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

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'task_id' => $task,
                'panel' => $data['panel'],
                'status' => $statusMap[$data['panel']],
                'message' => 'Task movida com sucesso.',
            ]);
        }

        return redirect()->back()->with('success', 'Task movida com sucesso.');
    }

    public function updateTaskMatrix(Request $request, Project $project, int $task)
    {
        abort_unless(Schema::hasTable('wt_project_tasks'), 404);

        $data = $request->validate([
            'importance' => ['required', 'integer', 'min:1', 'max:5'],
            'urgency' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $record = DB::table('wt_project_tasks')
            ->where('id', $task)
            ->where($this->projectColumn('wt_project_tasks'), $project->id)
            ->first();
        abort_unless($record, 404);

        $update = [];
        if (Schema::hasColumn('wt_project_tasks', 'importance')) {
            $update['importance'] = $data['importance'];
        }
        if (Schema::hasColumn('wt_project_tasks', 'urgency')) {
            $update['urgency'] = $data['urgency'];
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

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'task_id' => $task,
                'importance' => (int) $data['importance'],
                'urgency' => (int) $data['urgency'],
                'priority_score' => ((int) $data['importance'] * 2) + (int) $data['urgency'],
                'message' => 'Prioridade atualizada.',
            ]);
        }

        return redirect()->back()->with('success', 'Prioridade atualizada.');
    }

    public function completeMilestone(Request $request, Project $project, int $milestone)
    {
        abort_unless(Schema::hasTable('wt_project_tasks'), 404);

        $projectColumn = $this->projectColumn('wt_project_tasks');
        $milestoneRecord = DB::table('wt_project_tasks')
            ->where('id', $milestone)
            ->where($projectColumn, $project->id)
            ->first();

        abort_unless($milestoneRecord, 404);

        if (Schema::hasColumn('wt_project_tasks', 'type') && ($milestoneRecord->type ?? null) !== 'milestone') {
            return redirect()->back()->with('error', 'O item selecionado não é um milestone.');
        }

        $progress = $this->milestoneProgress($project->id, $milestone);
        if (($progress['open'] ?? 0) > 0) {
            return redirect()->back()->with('error', 'Ainda existem tasks abertas neste milestone.');
        }

        $update = [];
        if (Schema::hasColumn('wt_project_tasks', 'status')) {
            $update['status'] = 'done';
        }
        if (Schema::hasColumn('wt_project_tasks', 'completed_at')) {
            $update['completed_at'] = now();
        }
        if (Schema::hasColumn('wt_project_tasks', 'updated_at')) {
            $update['updated_at'] = now();
        }
        if ($update) {
            DB::table('wt_project_tasks')->where('id', $milestone)->update($update);
        }

        $nextMilestone = $this->activeMilestone($project->id);
        if ($nextMilestone) {
            $nextUpdate = [];
            if (Schema::hasColumn('wt_project_tasks', 'status') && !in_array(($nextMilestone->status ?? ''), ['in_progress', 'review'], true)) {
                $nextUpdate['status'] = 'in_progress';
            }
            if (Schema::hasColumn('wt_project_tasks', 'started_at') && empty($nextMilestone->started_at)) {
                $nextUpdate['started_at'] = now();
            }
            if (Schema::hasColumn('wt_project_tasks', 'start_date') && empty($nextMilestone->start_date)) {
                $nextUpdate['start_date'] = now();
            }
            if (Schema::hasColumn('wt_project_tasks', 'updated_at')) {
                $nextUpdate['updated_at'] = now();
            }
            if ($nextUpdate) {
                DB::table('wt_project_tasks')->where('id', $nextMilestone->id)->update($nextUpdate);
            }

            return redirect()->back()->with('success', 'Milestone concluído. O próximo milestone foi iniciado.');
        }

        return redirect()->back()->with('success', 'Milestone concluído. Não existem mais milestones abertos neste projeto.');
    }

    public function details(Project $project)
    {
        $sections = ProjectManagerSectionRegistry::all();
        $summary = $this->sectionSummary($project);

        $groups = [
            'Estrutura do projeto' => ['modules', 'links', 'contacts', 'external-dependencies'],
            'Identidade visual' => ['design-profiles', 'design-tokens', 'assets'],
            'Base técnica' => ['technical-stack', 'environments', 'guidelines'],
            'Documentação e decisões' => ['documentation', 'decisions', 'notes', 'blocks', 'activity'],
        ];

        $detailRecords = [];
        foreach ($sections as $key => $meta) {
            $detailRecords[$key] = $this->projectRecords($meta['table'], $project->id, 8);
        }

        $assetRecords = $detailRecords['assets'] ?? collect();
        $primaryLogo = $assetRecords->first(function ($asset) {
            return in_array(($asset->type ?? ''), ['logo', 'icon', 'image'], true) && !empty($asset->public_url) && (int)($asset->is_primary ?? 0) === 1;
        }) ?: $assetRecords->first(function ($asset) {
            return in_array(($asset->type ?? ''), ['logo', 'icon', 'image'], true) && !empty($asset->public_url);
        });

        $formOptions = $this->detailFormOptions($project);
        $fieldDefaults = $this->detailFieldDefaults($project);

        return $this->view('project-manager::projects.details', compact('project', 'sections', 'summary', 'groups', 'detailRecords', 'assetRecords', 'primaryLogo', 'formOptions', 'fieldDefaults'));
    }


    private function detailFormOptions(Project $project): array
    {
        $options = [];

        $fetch = function (string $table, array $labelColumns = ['name', 'title', 'label'], ?callable $filter = null) use ($project) {
            if (!Schema::hasTable($table)) {
                return collect();
            }

            $query = DB::table($table)->where($this->projectColumn($table), $project->id);
            if ($filter) {
                $filter($query);
            }
            if (Schema::hasColumn($table, 'execution_order')) {
                $query->orderBy('execution_order');
            }
            if (Schema::hasColumn($table, 'name')) {
                $query->orderBy('name');
            } elseif (Schema::hasColumn($table, 'title')) {
                $query->orderBy('title');
            }

            return $query->limit(300)->get()->mapWithKeys(function ($item) use ($labelColumns) {
                $label = null;
                foreach ($labelColumns as $column) {
                    if (!empty($item->{$column})) {
                        $label = $item->{$column};
                        break;
                    }
                }
                $label = $label ?: ('#' . $item->id);
                return [$item->id => $label];
            });
        };

        $modules = $fetch('wt_project_modules');
        $tasks = $fetch('wt_project_tasks', ['title', 'name'], function ($query) {
            if (Schema::hasColumn('wt_project_tasks', 'type')) {
                $query->where(function ($q) {
                    $q->whereNull('type')->orWhere('type', '<>', 'milestone');
                });
            }
        });
        $milestones = $fetch('wt_project_tasks', ['title', 'name'], function ($query) {
            if (Schema::hasColumn('wt_project_tasks', 'type')) {
                $query->where('type', 'milestone');
            }
        });
        $documentation = $fetch('wt_project_documentation_sections', ['title', 'name']);
        $roadmapItems = $fetch('wt_project_roadmap_items', ['title', 'name']);
        $assets = $fetch('wt_project_assets', ['name', 'title']);

        foreach (ProjectManagerSectionRegistry::all() as $section => $meta) {
            $options[$section] = [];
            foreach (($meta['selects'] ?? []) as $field => $values) {
                $options[$section][$field] = collect($values)->mapWithKeys(fn ($value) => [$value => ucfirst(str_replace('_', ' ', (string) $value))])->toArray();
            }
        }

        foreach (array_keys(ProjectManagerSectionRegistry::all()) as $section) {
            if ($modules->isNotEmpty()) {
                $options[$section]['project_module_id'] = $modules->toArray();
            }
        }

        $options['modules']['parent_id'] = $modules->toArray();
        $options['documentation']['parent_id'] = $documentation->toArray();
        $options['roadmap-items']['parent_id'] = $roadmapItems->toArray();
        $options['roadmap-items']['depends_on_id'] = $roadmapItems->toArray();
        $options['tasks']['parent_id'] = $milestones->union($tasks)->toArray();
        $options['tasks']['roadmap_group_id'] = $roadmapItems->toArray();
        $options['task-dependencies']['task_id'] = $tasks->toArray();
        $options['task-dependencies']['depends_on_task_id'] = $tasks->toArray();
        $options['task-blocks']['project_task_id'] = $tasks->toArray();
        $options['notes']['project_task_id'] = $tasks->toArray();
        $options['notes']['asset_id'] = $assets->toArray();

        return $options;
    }

    private function detailFieldDefaults(Project $project): array
    {
        $moduleId = null;
        if (Schema::hasTable('wt_project_modules')) {
            $moduleId = DB::table('wt_project_modules')
                ->where($this->projectColumn('wt_project_modules'), $project->id)
                ->when(Schema::hasColumn('wt_project_modules', 'execution_order'), fn ($q) => $q->orderBy('execution_order'))
                ->value('id');
        }

        return [
            'project_module_id' => $moduleId,
            'status' => 'active',
            'is_active' => 1,
            'is_required' => 1,
            'is_primary' => 0,
            'is_pinned' => 0,
            'visibility' => 'internal',
            'importance' => 'medium',
            'priority' => 5,
            'execution_order' => 10,
            'type' => 'general_note',
        ];
    }

    public function updateStatus(Request $request, Project $project)
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:in_progress,hold,pending,done'],
        ]);

        $project->status = $data['status'];
        $project->updated_at = now();
        $project->save();

        return redirect()->route('project_manager.projects.details', $project->id)->with('success', 'Estado do projeto atualizado.');
    }

    public function uploadAsset(Request $request, Project $project)
    {
        abort_unless(Schema::hasTable('wt_project_assets'), 404);

        $data = $request->validate([
            'asset_file' => ['required', 'file', 'max:10240'],
            'type' => ['required', 'string', 'max:50'],
            'name' => ['nullable', 'string', 'max:180'],
            'variant' => ['nullable', 'string', 'max:80'],
            'language' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_primary' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('asset_file');

        // Important: capture all metadata before moving the uploaded file.
        // On Windows/XAMPP the temporary file can no longer be stat'ed after move(),
        // which causes SplFileInfo::getSize(): stat failed for C:\xampp\tmp\phpXXXX.tmp.
        $originalName = $file->getClientOriginalName();
        $originalBaseName = pathinfo($originalName, PATHINFO_FILENAME);
        $originalMimeType = $file->getClientMimeType();
        $originalSize = null;
        try {
            $originalSize = $file->getSize();
        } catch (\Throwable $e) {
            $originalSize = null;
        }

        $safeName = Str::slug($originalBaseName);
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $fileName = ($safeName ?: 'asset') . '-' . now()->format('YmdHis') . '-' . Str::random(5) . '.' . $extension;
        $relativeDir = 'uploads/project-manager/' . $project->id;
        $absoluteDir = public_path($relativeDir);
        if (!is_dir($absoluteDir)) {
            mkdir($absoluteDir, 0755, true);
        }
        $file->move($absoluteDir, $fileName);

        $relativePath = $relativeDir . '/' . $fileName;
        $absolutePath = public_path($relativePath);
        $publicUrl = asset($relativePath);
        $dimensions = @getimagesize($absolutePath);
        $storedSize = $originalSize;
        if ($storedSize === null && is_file($absolutePath)) {
            $storedSize = @filesize($absolutePath) ?: null;
        }

        if (!empty($data['is_primary']) && Schema::hasColumn('wt_project_assets', 'is_primary')) {
            DB::table('wt_project_assets')
                ->where($this->projectColumn('wt_project_assets'), $project->id)
                ->where('type', $data['type'])
                ->update(['is_primary' => 0]);
        }

        $insert = [
            $this->projectColumn('wt_project_assets') => $project->id,
            'type' => $data['type'],
            'name' => $data['name'] ?: $originalBaseName,
            'variant' => $data['variant'] ?? null,
            'language' => $data['language'] ?? null,
            'file_path' => $relativePath,
            'public_url' => $publicUrl,
            'mime_type' => $originalMimeType,
            'file_size' => $storedSize,
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'description' => $data['description'] ?? null,
            'is_primary' => !empty($data['is_primary']) ? 1 : 0,
            'execution_order' => $this->nextExecutionOrder('wt_project_assets', $this->projectColumn('wt_project_assets'), $project->id),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('wt_project_assets')->insert($this->filterColumns('wt_project_assets', $insert));

        if (!empty($data['is_primary']) && $data['type'] === 'logo' && Schema::hasColumn('wt_projects', 'logo')) {
            $project->logo = $publicUrl;
            $project->save();
        }

        return redirect()->route('project_manager.projects.details', $project->id)->with('success', 'Asset carregado com sucesso.');
    }

    public function milestoneTasks(Project $project, int $milestone)
    {
        $tasks = $this->childrenOfMilestone($project->id, $milestone, 200);
        return $this->view('project-manager::partials.milestone-tasks-tree', compact('project', 'tasks'));
    }

    public function blockTask(Request $request, Project $project, int $task)
    {
        abort_unless(Schema::hasTable('wt_project_tasks'), 404);

        $data = $request->validate([
            'block_type' => ['required', 'string', 'max:80'],
            'blocked_reason' => ['required', 'string', 'max:2000'],
            'dependency_id' => ['nullable', 'integer'],
        ]);

        $taskRecord = DB::table('wt_project_tasks')->where('id', $task)->where($this->projectColumn('wt_project_tasks'), $project->id)->first();
        abort_unless($taskRecord, 404);

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

        if (Schema::hasTable('wt_project_task_blocks')) {
            $block = [
                $this->projectColumn('wt_project_task_blocks') => $project->id,
                'title' => 'Bloqueio: ' . ($taskRecord->title ?? ('Task #' . $task)),
                'description' => $data['blocked_reason'],
                'block_type' => $data['block_type'],
                'status' => 'open',
                'execution_order' => $this->nextExecutionOrder('wt_project_task_blocks', $this->projectColumn('wt_project_task_blocks'), $project->id),
            ];
            if (Schema::hasColumn('wt_project_task_blocks', 'project_task_id')) {
                $block['project_task_id'] = $task;
            }
            if (Schema::hasColumn('wt_project_task_blocks', 'blocked_at')) {
                $block['blocked_at'] = now();
            }
            if (Schema::hasColumn('wt_project_task_blocks', 'created_at')) {
                $block['created_at'] = now();
            }
            if (Schema::hasColumn('wt_project_task_blocks', 'updated_at')) {
                $block['updated_at'] = now();
            }
            DB::table('wt_project_task_blocks')->insert($this->filterColumns('wt_project_task_blocks', $block));
        }

        if (!empty($data['dependency_id']) && Schema::hasTable('wt_project_task_dependencies')) {
            $dependencyUpdate = [];
            if (Schema::hasColumn('wt_project_task_dependencies', 'status')) {
                $dependencyUpdate['status'] = 'active';
            }
            if (Schema::hasColumn('wt_project_task_dependencies', 'notes')) {
                $dependencyUpdate['notes'] = $data['blocked_reason'];
            }
            if (Schema::hasColumn('wt_project_task_dependencies', 'updated_at')) {
                $dependencyUpdate['updated_at'] = now();
            }
            if ($dependencyUpdate) {
                DB::table('wt_project_task_dependencies')->where('id', $data['dependency_id'])->update($dependencyUpdate);
            }
        }

        return redirect()->back()->with('success', 'Task movida para bloqueio.');
    }

    public function edit(Project $project)
    {
        return $this->view('project-manager::projects.form', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $this->validatedData($request);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['updated_at'] = now();

        $project->update($this->filterColumns('wt_projects', $data));

        return redirect()->route('project_manager.projects.show', $project->id)->with('success', 'Projeto atualizado com sucesso.');
    }

    public function destroy(Project $project)
    {
        if (Schema::hasColumn('wt_projects', 'deleted_at')) {
            $project->update(['deleted_at' => now()]);
        } else {
            $project->delete();
        }

        return redirect()->route('project_manager.projects.index')->with('success', 'Projeto removido/arquivado.');
    }


    private function ensureMatrixDefaults(int $projectId): void
    {
        if (!Schema::hasTable('wt_project_tasks')) {
            return;
        }

        $hasImportance = Schema::hasColumn('wt_project_tasks', 'importance');
        $hasUrgency = Schema::hasColumn('wt_project_tasks', 'urgency');
        $hasScore = Schema::hasColumn('wt_project_tasks', 'priority_score');

        if (!$hasImportance && !$hasUrgency && !$hasScore) {
            return;
        }

        $projectColumn = $this->projectColumn('wt_project_tasks');
        $tasks = DB::table('wt_project_tasks')
            ->where($projectColumn, $projectId)
            ->when(Schema::hasColumn('wt_project_tasks', 'type'), fn ($q) => $q->where('type', '<>', 'milestone'))
            ->when(Schema::hasColumn('wt_project_tasks', 'status'), fn ($q) => $q->whereNotIn('status', array_merge($this->closedStatuses, ['blocked'])))
            ->limit(300)
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

    private function matrixTasks(int $projectId)
    {
        if (!Schema::hasTable('wt_project_tasks')) {
            return collect();
        }

        return DB::table('wt_project_tasks')
            ->where($this->projectColumn('wt_project_tasks'), $projectId)
            ->when(Schema::hasColumn('wt_project_tasks', 'type'), fn ($q) => $q->where('type', '<>', 'milestone'))
            ->when(Schema::hasColumn('wt_project_tasks', 'status'), fn ($q) => $q->whereNotIn('status', array_merge($this->closedStatuses, ['blocked'])))
            ->when(Schema::hasColumn('wt_project_tasks', 'priority_score'), fn ($q) => $q->orderByDesc('priority_score'))
            ->when(Schema::hasColumn('wt_project_tasks', 'importance'), fn ($q) => $q->orderByDesc('importance'))
            ->when(Schema::hasColumn('wt_project_tasks', 'urgency'), fn ($q) => $q->orderByDesc('urgency'))
            ->when(Schema::hasColumn('wt_project_tasks', 'execution_order'), fn ($q) => $q->orderBy('execution_order'))
            ->orderBy('id')
            ->limit(120)
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
        if (in_array($status, ['in_progress', 'review'], true)) {
            return 5;
        }
        if (in_array($status, ['ready', 'waiting'], true)) {
            return 3;
        }
        return 2;
    }

    private function activeMilestone(int $projectId)
    {
        if (!Schema::hasTable('wt_project_tasks')) {
            return null;
        }

        $query = DB::table('wt_project_tasks')
            ->where($this->projectColumn('wt_project_tasks'), $projectId);

        if (Schema::hasColumn('wt_project_tasks', 'type')) {
            $query->where('type', 'milestone');
        } else {
            $query->where('parent_id', 0);
        }

        if (Schema::hasColumn('wt_project_tasks', 'status')) {
            $query->whereNotIn('status', $this->closedStatuses);
            $query->orderByRaw("FIELD(status, 'in_progress', 'ready', 'pending', 'waiting', 'blocked', 'review')");
        }

        return $query
            ->when(Schema::hasColumn('wt_project_tasks', 'execution_order'), fn ($q) => $q->orderBy('execution_order'))
            ->orderBy('id')
            ->first();
    }

    private function allMilestones(int $projectId, bool $includeClosed = false)
    {
        if (!Schema::hasTable('wt_project_tasks')) {
            return collect();
        }

        $query = DB::table('wt_project_tasks')
            ->where($this->projectColumn('wt_project_tasks'), $projectId);

        if (Schema::hasColumn('wt_project_tasks', 'type')) {
            $query->where('type', 'milestone');
        } else {
            $query->where('parent_id', 0);
        }

        if (!$includeClosed && Schema::hasColumn('wt_project_tasks', 'status')) {
            $query->whereNotIn('status', $this->closedStatuses);
        }

        return $query
            ->when(Schema::hasColumn('wt_project_tasks', 'execution_order'), fn ($q) => $q->orderBy('execution_order'))
            ->orderBy('id')
            ->get();
    }

    private function nextMilestones(int $projectId, ?int $activeMilestoneId, int $limit = 2)
    {
        $milestones = $this->allMilestones($projectId, false)->values();
        if (!$activeMilestoneId) {
            return $milestones->take($limit);
        }

        $index = $milestones->search(fn ($item) => (int) $item->id === $activeMilestoneId);
        if ($index === false) {
            return $milestones->take($limit);
        }

        return $milestones->slice($index + 1, $limit)->values();
    }

    private function childrenOfMilestone(int $projectId, int $milestoneId, int $limit = 50, ?array $statuses = null)
    {
        if (!Schema::hasTable('wt_project_tasks') || !Schema::hasColumn('wt_project_tasks', 'parent_id')) {
            return collect();
        }

        $query = DB::table('wt_project_tasks')
            ->where($this->projectColumn('wt_project_tasks'), $projectId)
            ->where('parent_id', $milestoneId);

        if ($statuses && Schema::hasColumn('wt_project_tasks', 'status')) {
            $query->whereIn('status', $statuses);
        }

        return $query
            ->when(Schema::hasColumn('wt_project_tasks', 'status'), fn ($q) => $q->orderByRaw("FIELD(status, 'in_progress', 'blocked', 'ready', 'review', 'waiting', 'pending', 'done', 'completed', 'cancelled')"))
            ->when(Schema::hasColumn('wt_project_tasks', 'priority'), fn ($q) => $q->orderBy('priority'))
            ->when(Schema::hasColumn('wt_project_tasks', 'execution_order'), fn ($q) => $q->orderBy('execution_order'))
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    private function milestoneProgress(int $projectId, int $milestoneId): array
    {
        $tasks = $this->childrenOfMilestone($projectId, $milestoneId, 1000);
        $total = $tasks->count();
        $closed = $tasks->filter(fn ($task) => in_array((string)($task->status ?? ''), $this->closedStatuses, true))->count();
        $open = max(0, $total - $closed);
        $percent = $total > 0 ? (int) round(($closed / $total) * 100) : 0;

        return [
            'total' => $total,
            'closed' => $closed,
            'open' => $open,
            'percent' => $percent,
            'can_complete' => $total > 0 && $open === 0,
        ];
    }

    private function sectionSummary(Project $project): array
    {
        $summary = [];
        foreach (ProjectManagerSectionRegistry::all() as $key => $section) {
            $summary[$key] = Schema::hasTable($section['table'])
                ? DB::table($section['table'])->where($this->projectColumn($section['table']), $project->id)->count()
                : 0;
        }
        return $summary;
    }

    private function projectRecords(string $table, int $projectId, int $limit = 10, ?callable $callback = null)
    {
        if (!Schema::hasTable($table)) {
            return collect();
        }

        $query = DB::table($table)->where($this->projectColumn($table), $projectId);

        if ($callback) {
            $callback($query);
        } elseif (Schema::hasColumn($table, 'updated_at')) {
            $query->orderByDesc('updated_at');
        }

        return $query->limit($limit)->get();
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180'],
            'code' => ['nullable', 'string', 'max:50'],
            'project_type' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'integer'],
            'description' => ['nullable', 'string'],
            'slogan' => ['nullable', 'string', 'max:180'],
            'logo' => ['nullable', 'string', 'max:500'],
            'url' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'string', 'max:500'],
            'repository_url' => ['nullable', 'string', 'max:500'],
            'documentation_url' => ['nullable', 'string', 'max:500'],
            'staging_url' => ['nullable', 'string', 'max:500'],
            'production_url' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'secondary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'font_family' => ['nullable', 'string', 'max:120'],
            'brand_notes' => ['nullable', 'string'],
            'structure_notes' => ['nullable', 'string'],
            'documentation_notes' => ['nullable', 'string'],
            'current_focus' => ['nullable', 'string', 'max:255'],
            'next_step' => ['nullable', 'string', 'max:255'],
            'health_status' => ['nullable', 'string', 'max:50'],
            'progress_percent' => ['nullable', 'numeric'],
            'start_date' => ['nullable', 'date'],
            'deadline' => ['nullable', 'date'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);
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

    private function filterColumns(string $table, array $data): array
    {
        return collect($data)->filter(fn ($value, $column) => Schema::hasColumn($table, $column))->all();
    }

    private function projectColumn(string $table): string
    {
        return Schema::hasColumn($table, 'project_id') ? 'project_id' : 'id_project';
    }
}
