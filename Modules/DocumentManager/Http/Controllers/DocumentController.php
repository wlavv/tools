<?php

namespace Modules\DocumentManager\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\DocumentManager\DTOs\CreateDocumentData;
use Modules\DocumentManager\Http\Requests\StoreDocumentRequest;
use Modules\DocumentManager\Http\Requests\UpdateDocumentRequest;
use Modules\DocumentManager\Models\Document;
use Modules\DocumentManager\Models\DocumentVersion;
use Modules\DocumentManager\Repositories\DocumentRepository;
use Modules\DocumentManager\Services\AiService;
use Modules\DocumentManager\Services\AuditService;
use Modules\DocumentManager\Services\DocumentService;
use Modules\DocumentManager\Services\EmbeddingService;
use Modules\DocumentManager\Services\OcrService;
use Modules\DocumentManager\Services\PreviewService;
use Modules\DocumentManager\Services\TimelineService;
use Modules\DocumentManager\Services\WorkflowService;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class DocumentController extends BaseDocumentController
{
    public function index(DocumentRepository $repository)
    {
        $documents = $repository->paginate([]);

        return view('documentmanager::documents.index', [
            'documents' => $documents,
            'missingTables' => DocumentTable::missingTables(),
        ]);
    }

    public function create()
    {
        return view('documentmanager::documents.create', $this->formData());
    }

    public function store(StoreDocumentRequest $request, DocumentService $service)
    {
        try {
            $document = $service->create(
                new CreateDocumentData($request->validated()),
                $request->file('file'),
                auth()->id()
            );

            return redirect()
                ->route('document-manager.documents.show', $document)
                ->with('success', 'Documento criado com sucesso.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['controller' => __CLASS__, 'method' => __METHOD__]);

            return redirect()
                ->route('document-manager.diagnostics.index')
                ->with('error', 'Nao foi possivel criar o documento. Ver diagnostics.');
        }
    }

    public function show($document, TimelineService $timeline)
    {
        $document = $this->resolveDocument($document);

        if (!$document) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Documento indisponivel ou tabelas em falta.');
        }

        $load = [];

        foreach ([
            'currentVersion' => 'document_core_versions',
            'workspace' => 'document_core_workspaces',
            'folder' => 'document_core_folders',
            'category' => 'document_core_categories',
        ] as $relation => $table) {
            if (DocumentTable::exists($table)) {
                $load[] = $relation;
            } else {
                $document->setRelation($relation, null);
            }
        }

        if (DocumentTable::exists('document_core_tags') && DocumentTable::exists('document_core_document_tags')) {
            $load[] = 'tags';
        }

        if (!empty($load)) {
            $document->load($load);
        }

        $currentVersion = $document->has_file ? $this->currentVersionFor($document) : null;
        $fileAvailable = $currentVersion && $this->versionFileExists($currentVersion);

        if (!$fileAvailable) {
            $this->disableDefaultAction('preview');
            $this->disableDefaultAction('download');
        }

        return view('documentmanager::documents.show', [
            'document' => $document,
            'fileAvailable' => $fileAvailable,
            'timeline' => $timeline->forDocument($document->id),
            'versions' => DocumentTable::safeGet('document_core_versions', function ($query) use ($document) {
                $query->where('document_id', $document->id)->orderByDesc('version_number');
            }),
            'relations' => DocumentTable::safeGet('document_core_relations', function ($query) use ($document) {
                $query->where('document_id', $document->id)->orderByDesc('id');
            }),
            'aiSummaries' => DocumentTable::safeGet('document_ai_summaries', function ($query) use ($document) {
                $query->where('document_id', $document->id)->orderByDesc('id');
            }),
            'ocrResults' => DocumentTable::safeGet('document_ai_ocr', function ($query) use ($document) {
                $query->where('document_id', $document->id)->orderByDesc('id')->limit(5);
            }),
            'aiAnalyses' => DocumentTable::safeGet('document_ai_analysis', function ($query) use ($document) {
                $query->where('document_id', $document->id)->orderByDesc('id')->limit(5);
            }),
        ]);
    }

    public function edit($document)
    {
        $document = $this->resolveDocument($document);

        if (!$document) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Documento indisponivel ou tabelas em falta.');
        }

        if (DocumentTable::exists('document_core_tags') && DocumentTable::exists('document_core_document_tags')) {
            $document->load('tags');
        } else {
            $document->setRelation('tags', collect());
        }

        $currentVersion = $document->has_file ? $this->currentVersionFor($document) : null;
        $fileAvailable = $currentVersion && $this->versionFileExists($currentVersion);

        return view('documentmanager::documents.edit', array_merge($this->formData(), [
            'document' => $document,
            'currentVersion' => $currentVersion,
            'fileAvailable' => $fileAvailable,
        ]));
    }

    public function update(UpdateDocumentRequest $request, $document, DocumentService $service)
    {
        $document = $this->resolveDocument($document);

        if (!$document) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Documento indisponivel ou tabelas em falta.');
        }

        try {
            $service->update($document, $request->validated(), auth()->id());

            return redirect()
                ->route('document-manager.documents.show', $document)
                ->with('success', 'Documento atualizado.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $document->id]);

            return back()->withInput()->with('error', 'Nao foi possivel atualizar o documento.');
        }
    }

    public function preview($document, PreviewService $preview)
    {
        $document = $this->resolveDocument($document);

        if (!$document) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Documento indisponivel ou tabelas em falta.');
        }

        $version = $this->currentVersionFor($document);
        $fileExists = $version && $this->versionFileExists($version);
        $canPreview = $fileExists && $preview->canPreview($version->mime_type ?: $document->mime_type);

        if (!$fileExists) {
            $this->disableDefaultAction('download');
        }

        return view('documentmanager::documents.preview', [
            'document' => $document,
            'version' => $version,
            'canPreview' => $canPreview,
            'mimeType' => $version?->mime_type ?: $document->mime_type,
            'previewUrl' => $canPreview ? route('document-manager.documents.file', $document->id) : null,
            'downloadUrl' => $version ? route('document-manager.documents.download', $document->id) : null,
        ]);
    }

    public function file($document)
    {
        $document = $this->resolveDocument($document);

        if (!$document) {
            abort(404, 'Documento indisponivel.');
        }

        $version = $this->currentVersionFor($document);

        if (!$version || !$this->versionFileExists($version)) {
            abort(404, 'Ficheiro indisponivel.');
        }

        $mimeType = $version->mime_type ?: $document->mime_type ?: 'application/octet-stream';

        if ($mimeType === 'text/html') {
            $mimeType = 'text/plain';
        }

        return Storage::disk($version->disk)->response(
            $version->path,
            $this->downloadName($document, $version),
            [
                'Content-Type' => $mimeType,
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, max-age=300',
            ],
            'inline'
        );
    }

    public function download($document)
    {
        $document = $this->resolveDocument($document);

        if (!$document) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Documento indisponivel ou tabelas em falta.');
        }

        $version = $this->currentVersionFor($document);

        if (!$version || !$this->versionFileExists($version)) {
            return back()->with('error', 'Ficheiro indisponivel para download.');
        }

        return Storage::disk($version->disk)->download(
            $version->path,
            $this->downloadName($document, $version),
            ['Content-Type' => $version->mime_type ?: 'application/octet-stream']
        );
    }

    public function process($document, string $operation, OcrService $ocr, AiService $ai, EmbeddingService $embeddings)
    {
        $document = $this->resolveDocument($document);

        if (!$document) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Documento indisponivel ou tabelas em falta.');
        }

        $version = $this->currentVersionFor($document);

        try {
            match ($operation) {
                'ocr' => $ocr->process($document->id, $version?->id),
                'summary' => $ai->summarize($document->id),
                'analysis' => $ai->analyze($document->id),
                'embeddings' => $embeddings->process($document->id, $version?->id),
                'all' => $this->processAll($document, $version, $ocr, $ai, $embeddings),
                default => throw new \InvalidArgumentException('Operacao de processamento invalida.'),
            };

            return back()->with('success', 'Processamento executado: ' . $operation);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $document->id, 'operation' => $operation]);

            return back()->with('error', 'Nao foi possivel executar o processamento.');
        }
    }

    public function workflow($document, WorkflowService $workflow)
    {
        $document = $this->resolveDocument($document);

        if (!$document) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Documento indisponivel ou tabelas em falta.');
        }

        $data = request()->validate([
            'workflow_state' => ['required', 'string', 'in:' . implode(',', config('documentmanager.workflow_states', []))],
        ]);

        try {
            $workflow->transition($document, $data['workflow_state'], auth()->id(), 'Manual transition from DocumentManager');

            return back()->with('success', 'Estado atualizado para ' . $data['workflow_state'] . '.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $document->id, 'state' => $data['workflow_state'] ?? null]);

            return back()->with('error', 'Nao foi possivel atualizar o estado.');
        }
    }

    public function destroy($document, AuditService $audit)
    {
        $document = $this->resolveDocument($document);

        if (!$document) {
            return redirect()->route('document-manager.diagnostics.index')
                ->with('error', 'Documento indisponivel ou tabelas em falta.');
        }

        try {
            $documentId = $document->id;
            $audit->activity($documentId, 'document.deleted', ['title' => $document->title], auth()->id());
            $document->delete();

            return redirect()->route('document-manager.documents.index')
                ->with('success', 'Documento removido.');
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $document->id]);

            return back()->with('error', 'Nao foi possivel remover o documento.');
        }
    }

    private function formData(): array
    {
        return [
            'workspaces' => $this->lookup('document_core_workspaces', ['id', 'name']),
            'folders' => $this->lookup('document_core_folders', ['id', 'name']),
            'categories' => $this->lookup('document_core_categories', ['id', 'name']),
            'tags' => $this->lookup('document_core_tags', ['id', 'name']),
        ];
    }

    private function processAll(Document $document, ?DocumentVersion $version, OcrService $ocr, AiService $ai, EmbeddingService $embeddings): void
    {
        $ocrResult = $ocr->process($document->id, $version?->id);
        $text = $ocrResult['text'] ?? null;

        $ai->summarize($document->id, $text);
        $ai->analyze($document->id, $text);
        $embeddings->process($document->id, $version?->id);
    }

    private function lookup(string $table, array $columns)
    {
        if (!DocumentTable::exists($table)) {
            return collect();
        }

        try {
            return DB::table($table)->select($columns)->orderBy('name')->get();
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['table' => $table]);

            return collect();
        }
    }

    private function resolveDocument($document): ?Document
    {
        if ($document instanceof Document) {
            return $document;
        }

        if (!DocumentTable::exists('document_core_documents')) {
            return null;
        }

        try {
            return Document::query()->find($document);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document' => $document]);

            return null;
        }
    }

    private function currentVersionFor(Document $document): ?DocumentVersion
    {
        if (!DocumentTable::exists('document_core_versions')) {
            return null;
        }

        try {
            if ($document->relationLoaded('currentVersion') && $document->currentVersion) {
                return $document->currentVersion;
            }

            if ($document->current_version_id) {
                $version = DocumentVersion::query()->find($document->current_version_id);

                if ($version) {
                    return $version;
                }
            }

            return DocumentVersion::query()
                ->where('document_id', $document->id)
                ->orderByDesc('version_number')
                ->orderByDesc('id')
                ->first();
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['document_id' => $document->id]);

            return null;
        }
    }

    private function versionFileExists(DocumentVersion $version): bool
    {
        try {
            return !empty($version->disk)
                && !empty($version->path)
                && Storage::disk($version->disk)->exists($version->path);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['version_id' => $version->id]);

            return false;
        }
    }

    private function downloadName(Document $document, DocumentVersion $version): string
    {
        if (!empty($version->original_name)) {
            return $version->original_name;
        }

        $base = $document->slug ?: 'document-' . $document->id;
        $extension = $version->extension ? '.' . ltrim($version->extension, '.') : '';

        return $base . $extension;
    }
}
