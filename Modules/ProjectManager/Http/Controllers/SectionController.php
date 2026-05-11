<?php

namespace Modules\ProjectManager\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\ProjectManager\Models\Project;
use Modules\ProjectManager\Services\ProjectManagerSectionRegistry;

class SectionController extends Controller

{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = $this->resolvePageTitle();
    }

    public function index(Request $request, Project $project)
    {
        $section = $this->sectionFromRoute($request);
        $meta = $this->sectionOrFail($section);
        $table = $meta['table'];
        $projectColumn = $this->projectColumn($table);

        abort_unless(Schema::hasTable($table), 404, 'Tabela não encontrada: ' . $table);

        $records = DB::table($table)
            ->where($projectColumn, $project->id)
            ->when(Schema::hasColumn($table, 'execution_order'), fn ($q) => $q->orderBy('execution_order'))
            ->when(Schema::hasColumn($table, 'created_at'), fn ($q) => $q->orderByDesc('created_at'))
            ->get();

        return $this->view('project-manager::sections.index', compact('project', 'section', 'meta', 'records'));
    }

    public function create(Request $request, Project $project)
    {
        $section = $this->sectionFromRoute($request);
        $meta = $this->sectionOrFail($section);
        $record = null;

        return $this->view('project-manager::sections.form', compact('project', 'section', 'meta', 'record'));
    }

    public function store(Request $request, Project $project)
    {
        $section = $this->sectionFromRoute($request);
        $meta = $this->sectionOrFail($section);
        $table = $meta['table'];
        abort_unless(Schema::hasTable($table), 404, 'Tabela não encontrada: ' . $table);

        $data = $this->buildData($request, $meta, $table);
        $projectColumn = $this->projectColumn($table);
        $data[$projectColumn] = $project->id;
        $this->ensureExecutionOrder($data, $table, $projectColumn, $project->id);
        if (Schema::hasColumn($table, 'created_at')) {
            $data['created_at'] = now();
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table($table)->insert($data);

        if ($request->boolean('return_to_details')) {
            return redirect()->to(route('project_manager.projects.details', $project->id) . '#details-' . $section)
                ->with('success', 'Registo criado com sucesso.');
        }

        return redirect()->route(ProjectManagerSectionRegistry::routeName($section, 'index'), $project->id)->with('success', 'Registo criado com sucesso.');
    }

    public function edit(Request $request, Project $project, int $id)
    {
        $section = $this->sectionFromRoute($request);
        $meta = $this->sectionOrFail($section);
        $table = $meta['table'];
        $record = DB::table($table)->where('id', $id)->first();
        abort_unless($record, 404);

        return $this->view('project-manager::sections.form', compact('project', 'section', 'meta', 'record'));
    }

    public function update(Request $request, Project $project, int $id)
    {
        $section = $this->sectionFromRoute($request);
        $meta = $this->sectionOrFail($section);
        $table = $meta['table'];
        $data = $this->buildData($request, $meta, $table);
        $projectColumn = $this->projectColumn($table);
        $this->ensureExecutionOrder($data, $table, $projectColumn, $project->id, false);
        if (Schema::hasColumn($table, 'updated_at')) {
            $data['updated_at'] = now();
        }

        DB::table($table)->where('id', $id)->update($data);

        return redirect()->route(ProjectManagerSectionRegistry::routeName($section, 'index'), $project->id)->with('success', 'Registo atualizado com sucesso.');
    }

    public function destroy(Request $request, Project $project, int $id)
    {
        $section = $this->sectionFromRoute($request);
        $meta = $this->sectionOrFail($section);
        $table = $meta['table'];

        if (Schema::hasColumn($table, 'deleted_at')) {
            DB::table($table)->where('id', $id)->update(['deleted_at' => now()]);
        } else {
            DB::table($table)->where('id', $id)->delete();
        }

        return redirect()->route(ProjectManagerSectionRegistry::routeName($section, 'index'), $project->id)->with('success', 'Registo removido.');
    }

    private function sectionFromRoute(Request $request): string
    {
        return (string) $request->route('section');
    }

    private function sectionOrFail(string $section): array
    {
        $meta = ProjectManagerSectionRegistry::get($section);
        abort_unless($meta, 404, 'Secção inválida: ' . $section);
        return $meta;
    }

    private function buildData(Request $request, array $meta, string $table): array
    {
        $data = [];
        $fields = $meta['fields'] ?? [];
        $booleans = $meta['booleans'] ?? [];

        foreach ($fields as $field) {
            if (!Schema::hasColumn($table, $field)) {
                continue;
            }

            $data[$field] = in_array($field, $booleans, true)
                ? (int) $request->boolean($field)
                : $request->input($field);
        }

        return $data;
    }

    private function ensureExecutionOrder(array &$data, string $table, string $projectColumn, int $projectId, bool $forceWhenMissing = true): void
    {
        if (!Schema::hasColumn($table, 'execution_order')) {
            return;
        }

        $current = $data['execution_order'] ?? null;
        if ($current !== null && $current !== '') {
            $data['execution_order'] = (int) $current;
            return;
        }

        if (!$forceWhenMissing && !array_key_exists('execution_order', $data)) {
            return;
        }

        $max = DB::table($table)
            ->where($projectColumn, $projectId)
            ->max('execution_order');

        $data['execution_order'] = ((int) $max) + 1;
    }

    private function projectColumn(string $table): string
    {
        if (!Schema::hasTable($table)) {
            return 'project_id';
        }

        return Schema::hasColumn($table, 'project_id') ? 'project_id' : 'id_project';
    }
}
