<?php

namespace Modules\ProjectManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\ProjectManager\Models\Project;

class ProjectCreationFromIdeaService
{
    public function createFromIdeaPayload(array $payload): Project
    {
        return DB::transaction(function () use ($payload) {
            $project = Project::query()->create($this->filterColumns('wt_projects', [
                'name' => (string) data_get($payload, 'name', 'IdeaLab Project'),
                'slug' => $this->uniqueProjectSlug((string) data_get($payload, 'name', 'idealab-project')),
                'project_type' => 'idealab',
                'status' => 'pending',
                'priority' => $this->mapPriority(data_get($payload, 'priority')),
                'description' => (string) data_get($payload, 'description', ''),
                'structure_notes' => $this->formatStructureNotes($payload),
                'documentation_notes' => $this->stringify(data_get($payload, 'brief')),
                'current_focus' => 'IdeaLab conversion',
                'next_step' => 'Review generated milestones and start the first actionable task.',
                'progress_percent' => 0,
                'health_status' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            $moduleId = $this->createConceptModules($project, $payload);
            $milestones = $this->normaliseMilestones($payload);
            $previousMilestoneId = null;

            foreach ($milestones as $index => $milestone) {
                $milestoneId = $this->insertTask($project->id, [
                    'project_module_id' => $moduleId,
                    'parent_id' => 0,
                    'type' => 'milestone',
                    'title' => $milestone['title'],
                    'description' => $milestone['description'],
                    'priority' => $index + 1,
                    'status' => $index === 0 ? 'in_progress' : 'pending',
                    'execution_order' => ($index + 1) * 100,
                    'source' => 'idealab',
                    'importance' => max(3, 5 - min(2, $index)),
                    'urgency' => $index === 0 ? 4 : 2,
                ]);

                $roadmapItemId = $this->insertRoadmapItem($project->id, $moduleId, $milestone, $index, $previousMilestoneId);

                foreach ($milestone['tasks'] as $taskIndex => $task) {
                    $this->insertTask($project->id, [
                        'project_module_id' => $moduleId,
                        'roadmap_item_id' => $roadmapItemId,
                        'parent_id' => $milestoneId,
                        'type' => $task['type'] ?? 'task',
                        'title' => $task['title'],
                        'description' => $task['description'] ?? null,
                        'priority' => $task['priority'] ?? ($taskIndex + 1),
                        'status' => $index === 0 && $taskIndex === 0 ? 'ready' : 'pending',
                        'execution_order' => (($index + 1) * 100) + $taskIndex + 1,
                        'expected_time' => $task['expected_time'] ?? null,
                        'acceptance_criteria' => $task['acceptance_criteria'] ?? null,
                        'technical_notes' => $task['technical_notes'] ?? null,
                        'source' => 'idealab',
                        'importance' => $task['importance'] ?? $this->importanceFromPriority($task['priority'] ?? ($taskIndex + 1)),
                        'urgency' => $task['urgency'] ?? ($index === 0 ? 3 : 2),
                    ]);
                }

                $previousMilestoneId = $roadmapItemId;
            }

            $this->createDocumentation($project, $payload, $moduleId);
            $this->createDecisionsAndNotes($project, $payload, $moduleId);
            $this->createActivityLog($project, $payload);

            return $project->fresh();
        });
    }

    protected function createConceptModules(Project $project, array $payload): ?int
    {
        if (!Schema::hasTable('wt_project_modules')) {
            return null;
        }

        $concepts = $this->normaliseList(data_get($payload, 'structure.concepts'));

        if (empty($concepts)) {
            $concepts = [
                ['title' => (string) data_get($payload, 'category', 'Core Concept'), 'description' => data_get($payload, 'structure.value_proposition')],
            ];
        }

        $firstId = null;
        foreach (array_values($concepts) as $index => $concept) {
            $title = $this->itemTitle($concept, 'Concept ' . ($index + 1));
            $id = DB::table('wt_project_modules')->insertGetId($this->filterColumns('wt_project_modules', [
                'project_id' => $project->id,
                'name' => $title,
                'slug' => Str::slug($title),
                'description' => $this->itemDescription($concept),
                'technical_notes' => $this->stringify($concept),
                'status' => $index === 0 ? 'in_progress' : 'planned',
                'priority' => $index + 1,
                'execution_order' => ($index + 1) * 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            $firstId ??= $id;
        }

        return $firstId;
    }

    protected function normaliseMilestones(array $payload): array
    {
        $source = $this->normaliseList(data_get($payload, 'structure.milestones'));

        if (empty($source)) {
            $source = [
                [
                    'title' => 'Discovery and Scope',
                    'description' => 'Clarify problem, audience, value proposition, boundaries and success criteria.',
                    'tasks' => [
                        ['title' => 'Validate problem and target users', 'type' => 'research'],
                        ['title' => 'Define MVP scope and non-goals', 'type' => 'documentation'],
                        ['title' => 'Identify risks, dependencies and blockers', 'type' => 'research'],
                    ],
                ],
                [
                    'title' => 'Architecture and Backlog',
                    'description' => 'Split the idea into technical concepts, modules, data structures and execution priorities.',
                    'tasks' => [
                        ['title' => 'Create technical architecture brief', 'type' => 'documentation'],
                        ['title' => 'Break MVP into implementation tasks', 'type' => 'task'],
                        ['title' => 'Prioritise execution order', 'type' => 'task'],
                    ],
                ],
                [
                    'title' => 'MVP Implementation',
                    'description' => 'Build the smallest useful version and prepare validation.',
                    'tasks' => [
                        ['title' => 'Implement core workflow', 'type' => 'feature'],
                        ['title' => 'Add acceptance tests or manual validation checklist', 'type' => 'test'],
                        ['title' => 'Review and prepare next iteration', 'type' => 'review'],
                    ],
                ],
            ];
        }

        return array_values(array_map(function ($milestone, $index) use ($payload) {
            $tasks = $this->normaliseList(data_get($milestone, 'tasks'));

            if (empty($tasks)) {
                $tasks = $this->tasksForMilestone($milestone, $payload);
            }

            return [
                'title' => $this->itemTitle($milestone, 'Milestone ' . ($index + 1)),
                'description' => $this->itemDescription($milestone),
                'tasks' => array_values(array_map(fn ($task, $taskIndex) => [
                    'title' => $this->itemTitle($task, 'Task ' . ($taskIndex + 1)),
                    'description' => $this->itemDescription($task),
                    'type' => data_get($task, 'type', 'task'),
                    'priority' => (int) data_get($task, 'priority', $taskIndex + 1),
                    'importance' => data_get($task, 'importance'),
                    'urgency' => data_get($task, 'urgency'),
                    'expected_time' => data_get($task, 'expected_time'),
                    'acceptance_criteria' => $this->stringify(data_get($task, 'acceptance_criteria')),
                    'technical_notes' => $this->stringify(data_get($task, 'technical_notes')),
                ], $tasks, array_keys($tasks))),
            ];
        }, $source, array_keys($source)));
    }

    protected function tasksForMilestone(mixed $milestone, array $payload): array
    {
        $title = Str::lower($this->itemTitle($milestone, ''));

        if (str_contains($title, 'mvp')) {
            return $this->listToTasks(data_get($payload, 'structure.mvp'), 'feature');
        }

        if (str_contains($title, 'technical') || str_contains($title, 'architecture')) {
            return $this->listToTasks(data_get($payload, 'structure.technical_requirements'), 'setup');
        }

        if (str_contains($title, 'risk')) {
            return $this->listToTasks(data_get($payload, 'structure.risks'), 'research');
        }

        return [['title' => 'Define deliverables for ' . $this->itemTitle($milestone, 'this milestone'), 'type' => 'task']];
    }

    protected function listToTasks(mixed $items, string $type): array
    {
        $list = $this->normaliseList($items);

        if (empty($list)) {
            return [['title' => 'Define and execute next step', 'type' => $type]];
        }

        return array_map(fn ($item) => [
            'title' => $this->itemTitle($item, 'Task'),
            'description' => $this->itemDescription($item),
            'type' => $type,
        ], $list);
    }

    protected function insertRoadmapItem(int $projectId, ?int $moduleId, array $milestone, int $index, ?int $previousId): ?int
    {
        if (!Schema::hasTable('wt_project_roadmap_items')) {
            return null;
        }

        return DB::table('wt_project_roadmap_items')->insertGetId($this->filterColumns('wt_project_roadmap_items', [
            'project_id' => $projectId,
            'project_module_id' => $moduleId,
            'title' => $milestone['title'],
            'description' => $milestone['description'],
            'status' => $index === 0 ? 'in_progress' : 'pending',
            'priority' => $index + 1,
            'depends_on_item_id' => $previousId,
            'execution_order' => ($index + 1) * 100,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    protected function insertTask(int $projectId, array $data): int
    {
        return DB::table('wt_project_tasks')->insertGetId($this->filterColumns('wt_project_tasks', array_merge([
            'project_id' => $projectId,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ], $data)));
    }

    protected function createDocumentation(Project $project, array $payload, ?int $moduleId): void
    {
        if (!Schema::hasTable('wt_project_documentation_sections')) {
            return;
        }

        $sections = [
            ['type' => 'overview', 'title' => 'IdeaLab Brief', 'summary' => data_get($payload, 'structure.executive_summary'), 'content' => $this->formatStructureNotes($payload)],
            ['type' => 'architecture', 'title' => 'Concepts and Complexity', 'summary' => 'Concept split, complexity and technical requirements.', 'content' => $this->stringify([
                'concepts' => data_get($payload, 'structure.concepts'),
                'complexity' => data_get($payload, 'structure.complexity'),
                'technical_requirements' => data_get($payload, 'structure.technical_requirements'),
            ])],
        ];

        foreach ($sections as $index => $section) {
            DB::table('wt_project_documentation_sections')->insert($this->filterColumns('wt_project_documentation_sections', array_merge($section, [
                'project_id' => $project->id,
                'project_module_id' => $moduleId,
                'status' => 'active',
                'is_pinned' => $index === 0 ? 1 : 0,
                'execution_order' => ($index + 1) * 10,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ])));
        }
    }

    protected function createDecisionsAndNotes(Project $project, array $payload, ?int $moduleId): void
    {
        if (Schema::hasTable('wt_project_decisions')) {
            DB::table('wt_project_decisions')->insert($this->filterColumns('wt_project_decisions', [
                'project_id' => $project->id,
                'project_module_id' => $moduleId,
                'title' => 'Converted from IdeaLab',
                'context' => 'The idea reached project-candidate stage in IdeaLab.',
                'decision' => 'Create Project Manager project with generated milestones and execution tasks.',
                'reason' => data_get($payload, 'structure.recommendation'),
                'impact' => 'Project Manager is now the operational source of truth for implementation.',
                'status' => 'accepted',
                'decided_by' => auth()->id(),
                'decided_at' => now(),
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        if (Schema::hasTable('wt_project_notes')) {
            DB::table('wt_project_notes')->insert($this->filterColumns('wt_project_notes', [
                'project_id' => $project->id,
                'project_module_id' => $moduleId,
                'type' => 'general_note',
                'title' => 'Original IdeaLab payload',
                'content' => $this->stringify($payload),
                'visibility' => 'internal',
                'is_pinned' => 1,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    protected function createActivityLog(Project $project, array $payload): void
    {
        if (!Schema::hasTable('wt_project_activity_logs')) {
            return;
        }

        DB::table('wt_project_activity_logs')->insert($this->filterColumns('wt_project_activity_logs', [
            'project_id' => $project->id,
            'entity_type' => 'idealab_idea',
            'entity_id' => data_get($payload, 'source_id'),
            'action' => 'created_from_idea',
            'title' => 'Project created from IdeaLab',
            'description' => 'Generated project structure, roadmap milestones and initial task backlog from an IdeaLab idea.',
            'new_values_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
            'user_id' => auth()->id(),
            'user_name' => optional(auth()->user())->name,
            'created_at' => now(),
        ]));
    }

    protected function normaliseList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values($value);
        }

        if (is_string($value) && trim($value) !== '') {
            return preg_split('/\\r?\\n|;/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        }

        return [];
    }

    protected function itemTitle(mixed $item, string $fallback): string
    {
        if (is_string($item)) {
            return Str::limit(trim($item), 140, '');
        }

        if (is_array($item)) {
            return Str::limit((string) (data_get($item, 'title') ?: data_get($item, 'name') ?: data_get($item, 'label') ?: $fallback), 140, '');
        }

        return $fallback;
    }

    protected function itemDescription(mixed $item): ?string
    {
        if (is_string($item)) {
            return null;
        }

        if (is_array($item)) {
            return $this->stringify(data_get($item, 'description') ?: data_get($item, 'summary') ?: data_get($item, 'content'));
        }

        return null;
    }

    protected function stringify(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        return $json === false ? null : $json;
    }

    protected function formatStructureNotes(array $payload): string
    {
        return trim(implode("\n\n", array_filter([
            '# Origin',
            'Source: ' . data_get($payload, 'source') . ' #' . data_get($payload, 'source_id'),
            '# Executive Summary',
            $this->stringify(data_get($payload, 'structure.executive_summary')),
            '# Problem',
            $this->stringify(data_get($payload, 'structure.problem')),
            '# Value Proposition',
            $this->stringify(data_get($payload, 'structure.value_proposition')),
            '# MVP',
            $this->stringify(data_get($payload, 'structure.mvp')),
            '# Risks',
            $this->stringify(data_get($payload, 'structure.risks')),
            '# Dependencies',
            $this->stringify(data_get($payload, 'structure.dependencies')),
        ])));
    }

    protected function mapPriority(mixed $priority): int
    {
        return match ((string) $priority) {
            'strategic', 'high' => 1,
            'medium' => 3,
            'low' => 5,
            default => is_numeric($priority) ? max(1, min(5, (int) $priority)) : 3,
        };
    }

    protected function importanceFromPriority(int $priority): int
    {
        if ($priority <= 1) {
            return 5;
        }

        if ($priority <= 3) {
            return 4;
        }

        return 3;
    }

    protected function uniqueProjectSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'idealab-project';
        $slug = $base;
        $index = 2;

        while (Project::query()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $index++;
        }

        return $slug;
    }

    protected function filterColumns(string $table, array $data): array
    {
        return collect($data)
            ->filter(fn ($value, $column) => Schema::hasColumn($table, $column))
            ->all();
    }
}
