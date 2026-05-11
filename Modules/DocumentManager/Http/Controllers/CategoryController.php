<?php

namespace Modules\DocumentManager\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class CategoryController extends BaseDocumentController
{
    public function index()
    {
        $categories = DocumentTable::safeGet('document_core_categories', function ($query) {
            if (DocumentTable::exists('document_core_workspaces')) {
                $query->leftJoin('document_core_workspaces as w', 'w.id', '=', 'document_core_categories.workspace_id')
                    ->select('document_core_categories.*', 'w.name as workspace_name');
            } else {
                $query->select('document_core_categories.*', DB::raw('NULL as workspace_name'));
            }

            $query->orderBy('document_core_categories.parent_id')
                ->orderBy('document_core_categories.name');
        });

        return view('documentmanager::categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return view('documentmanager::categories.create', $this->formData());
    }

    public function store()
    {
        if (!DocumentTable::exists('document_core_categories')) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Tabela de categorias em falta.');
        }

        $data = request()->validate($this->rules());

        try {
            DB::table('document_core_categories')->insert([
                'uuid' => (string) Str::uuid(),
                'workspace_id' => $data['workspace_id'] ?: null,
                'parent_id' => $data['parent_id'] ?: null,
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) ?: Str::random(10),
                'color' => $data['color'] ?: null,
                'icon' => $data['icon'] ?: 'fa-solid fa-folder',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('document-manager.categories.index')
                ->with('success', 'Categoria criada.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['controller' => __CLASS__]);

            return back()->withInput()->with('error', 'Nao foi possivel criar a categoria.');
        }
    }

    public function edit($category)
    {
        $category = $this->findCategory($category);

        if (!$category) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Categoria indisponivel.');
        }

        return view('documentmanager::categories.edit', array_merge($this->formData(), [
            'category' => $category,
        ]));
    }

    public function update($category)
    {
        $category = $this->findCategory($category);

        if (!$category) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Categoria indisponivel.');
        }

        $data = request()->validate($this->rules());

        try {
            DB::table('document_core_categories')
                ->where('id', $category->id)
                ->update([
                    'workspace_id' => $data['workspace_id'] ?: null,
                    'parent_id' => $data['parent_id'] ?: null,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']) ?: $category->slug,
                    'color' => $data['color'] ?: null,
                    'icon' => $data['icon'] ?: 'fa-solid fa-folder',
                    'updated_at' => now(),
                ]);

            return redirect()->route('document-manager.categories.index')
                ->with('success', 'Categoria atualizada.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['category_id' => $category->id]);

            return back()->withInput()->with('error', 'Nao foi possivel atualizar a categoria.');
        }
    }

    private function rules(): array
    {
        return [
            'workspace_id' => ['nullable', 'integer'],
            'parent_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:24'],
            'icon' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function formData(): array
    {
        return [
            'workspaces' => DocumentTable::safeGet('document_core_workspaces', fn ($query) => $query->orderBy('name')),
            'categories' => DocumentTable::safeGet('document_core_categories', fn ($query) => $query->orderBy('name')),
        ];
    }

    private function findCategory($id)
    {
        if (!DocumentTable::exists('document_core_categories')) {
            return null;
        }

        return DB::table('document_core_categories')->where('id', $id)->whereNull('deleted_at')->first();
    }
}
