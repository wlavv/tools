<?php

namespace Modules\DocumentManager\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class TagController extends BaseDocumentController
{
    public function index()
    {
        return view('documentmanager::tags.index', [
            'tags' => DocumentTable::safeGet('document_core_tags', fn ($query) => $query->orderBy('name')),
        ]);
    }

    public function create()
    {
        return view('documentmanager::tags.create');
    }

    public function store()
    {
        if (!DocumentTable::exists('document_core_tags')) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Tabela de tags em falta.');
        }

        $data = request()->validate($this->rules());

        try {
            DB::table('document_core_tags')->insert([
                'uuid' => (string) Str::uuid(),
                'name' => $data['name'],
                'slug' => Str::slug($data['name']) ?: Str::random(10),
                'type' => $data['type'] ?: 'manual',
                'color' => $data['color'] ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('document-manager.tags.index')
                ->with('success', 'Tag criada.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['controller' => __CLASS__]);

            return back()->withInput()->with('error', 'Nao foi possivel criar a tag.');
        }
    }

    public function edit($tag)
    {
        $tag = $this->findTag($tag);

        if (!$tag) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Tag indisponivel.');
        }

        return view('documentmanager::tags.edit', compact('tag'));
    }

    public function update($tag)
    {
        $tag = $this->findTag($tag);

        if (!$tag) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Tag indisponivel.');
        }

        $data = request()->validate($this->rules());

        try {
            DB::table('document_core_tags')
                ->where('id', $tag->id)
                ->update([
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']) ?: $tag->slug,
                    'type' => $data['type'] ?: 'manual',
                    'color' => $data['color'] ?: null,
                    'updated_at' => now(),
                ]);

            return redirect()->route('document-manager.tags.index')
                ->with('success', 'Tag atualizada.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['tag_id' => $tag->id]);

            return back()->withInput()->with('error', 'Nao foi possivel atualizar a tag.');
        }
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:120'],
            'color' => ['nullable', 'string', 'max:24'],
        ];
    }

    private function findTag($id)
    {
        if (!DocumentTable::exists('document_core_tags')) {
            return null;
        }

        return DB::table('document_core_tags')->where('id', $id)->whereNull('deleted_at')->first();
    }
}
