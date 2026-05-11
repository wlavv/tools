<?php

namespace Modules\DocumentManager\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class WorkspaceController extends BaseDocumentController
{
    public function index()
    {
        $workspaces = DocumentTable::safeGet('document_core_workspaces', function ($query) {
            $query->orderBy('name');
        });

        return view('documentmanager::workspaces.index', [
            'workspaces' => $workspaces,
        ]);
    }

    public function create()
    {
        return view('documentmanager::workspaces.create');
    }

    public function store()
    {
        if (!DocumentTable::exists('document_core_workspaces')) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Tabela de workspaces em falta.');
        }

        $data = request()->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            DB::table('document_core_workspaces')->insert([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) ?: Str::random(10),
                'type' => $data['type'] ?: 'operational',
                'icon' => $data['icon'] ?: 'fa-solid fa-layer-group',
                'description' => $data['description'] ?? null,
                'is_active' => request()->boolean('is_active', true),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('document-manager.workspaces.index')
                ->with('success', 'Workspace criado.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['controller' => __CLASS__]);

            return back()->withInput()->with('error', 'Nao foi possivel criar o workspace.');
        }
    }

    public function edit($workspace)
    {
        $workspace = $this->findWorkspace($workspace);

        if (!$workspace) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Workspace indisponivel.');
        }

        return view('documentmanager::workspaces.edit', compact('workspace'));
    }

    public function update($workspace)
    {
        $workspace = $this->findWorkspace($workspace);

        if (!$workspace) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Workspace indisponivel.');
        }

        $data = request()->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:120'],
            'icon' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        try {
            DB::table('document_core_workspaces')
                ->where('id', $workspace->id)
                ->update([
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']) ?: $workspace->slug,
                    'type' => $data['type'] ?: 'operational',
                    'icon' => $data['icon'] ?: 'fa-solid fa-layer-group',
                    'description' => $data['description'] ?? null,
                    'is_active' => request()->boolean('is_active'),
                    'updated_at' => now(),
                ]);

            return redirect()->route('document-manager.workspaces.index')
                ->with('success', 'Workspace atualizado.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['workspace_id' => $workspace->id]);

            return back()->withInput()->with('error', 'Nao foi possivel atualizar o workspace.');
        }
    }

    private function findWorkspace($id)
    {
        if (!DocumentTable::exists('document_core_workspaces')) {
            return null;
        }

        return DB::table('document_core_workspaces')->where('id', $id)->whereNull('deleted_at')->first();
    }
}
