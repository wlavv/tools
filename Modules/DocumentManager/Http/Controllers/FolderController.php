<?php

namespace Modules\DocumentManager\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class FolderController extends BaseDocumentController
{
    public function index()
    {
        $folders = DocumentTable::safeGet('document_core_folders', function ($query) {
            if (DocumentTable::exists('document_core_workspaces')) {
                $query->leftJoin('document_core_workspaces as w', 'w.id', '=', 'document_core_folders.workspace_id')
                    ->select('document_core_folders.*', 'w.name as workspace_name');
            } else {
                $query->select('document_core_folders.*', DB::raw('NULL as workspace_name'));
            }

            $query->orderBy('document_core_folders.path')->orderBy('document_core_folders.name');
        });

        return view('documentmanager::folders.index', compact('folders'));
    }

    public function create()
    {
        return view('documentmanager::folders.create', $this->formData());
    }

    public function store()
    {
        if (!DocumentTable::exists('document_core_folders')) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Tabela de folders em falta.');
        }

        $data = request()->validate($this->rules());

        try {
            DB::table('document_core_folders')->insert([
                'uuid' => (string) Str::uuid(),
                'workspace_id' => $data['workspace_id'] ?: null,
                'parent_id' => $data['parent_id'] ?: null,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) ?: Str::random(10),
                'path' => $data['path'] ?: $data['name'],
                'depth' => (int) ($data['depth'] ?? 0),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('document-manager.folders.index')
                ->with('success', 'Folder criado.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['controller' => __CLASS__]);

            return back()->withInput()->with('error', 'Nao foi possivel criar o folder.');
        }
    }

    public function edit($folder)
    {
        $folder = $this->findFolder($folder);

        if (!$folder) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Folder indisponivel.');
        }

        return view('documentmanager::folders.edit', array_merge($this->formData(), [
            'folder' => $folder,
        ]));
    }

    public function update($folder)
    {
        $folder = $this->findFolder($folder);

        if (!$folder) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Folder indisponivel.');
        }

        $data = request()->validate($this->rules());

        try {
            DB::table('document_core_folders')
                ->where('id', $folder->id)
                ->update([
                    'workspace_id' => $data['workspace_id'] ?: null,
                    'parent_id' => $data['parent_id'] ?: null,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']) ?: $folder->slug,
                    'path' => $data['path'] ?: $folder->path,
                    'depth' => (int) ($data['depth'] ?? $folder->depth),
                    'sort_order' => (int) ($data['sort_order'] ?? $folder->sort_order),
                    'updated_at' => now(),
                ]);

            return redirect()->route('document-manager.folders.index')
                ->with('success', 'Folder atualizado.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['folder_id' => $folder->id]);

            return back()->withInput()->with('error', 'Nao foi possivel atualizar o folder.');
        }
    }

    private function rules(): array
    {
        return [
            'workspace_id' => ['nullable', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'path' => ['nullable', 'string', 'max:255'],
            'depth' => ['nullable', 'integer'],
            'sort_order' => ['nullable', 'integer'],
        ];
    }

    private function formData(): array
    {
        return [
            'workspaces' => DocumentTable::safeGet('document_core_workspaces', fn ($query) => $query->orderBy('name')),
            'folders' => DocumentTable::safeGet('document_core_folders', fn ($query) => $query->orderBy('name')),
        ];
    }

    private function findFolder($id)
    {
        if (!DocumentTable::exists('document_core_folders')) {
            return null;
        }

        return DB::table('document_core_folders')->where('id', $id)->whereNull('deleted_at')->first();
    }
}
