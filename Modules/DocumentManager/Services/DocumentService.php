<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\DTOs\CreateDocumentData;
use Modules\DocumentManager\Exceptions\DocumentManagerException;
use Modules\DocumentManager\Jobs\ProcessDocumentPipeline;
use Modules\DocumentManager\Models\Document;
use Modules\DocumentManager\Models\DocumentVersion;
use Modules\DocumentManager\Support\DocumentTable;

class DocumentService
{
    public function __construct(
        private StorageService $storage,
        private AuditService $audit,
        private PreviewService $preview
    ) {
    }

    public function create(CreateDocumentData $data, ?UploadedFile $file = null, ?int $userId = null): Document
    {
        if (!DocumentTable::exists('document_core_documents')) {
            throw new DocumentManagerException('Document tables are not installed.');
        }

        $document = DB::transaction(function () use ($data, $file, $userId) {
            $document = Document::create(array_merge($data->toDocumentArray($userId), [
                'slug' => Str::slug($data->title) ?: null,
                'status' => 'draft',
                'workflow_state' => 'draft',
                'visibility' => 'private',
                'has_file' => (bool) $file,
                'source_module' => $data->sourceModule ?: 'document-manager',
                'metadata' => $this->normalizeMetadata($data->metadata),
            ]));

            if ($file && DocumentTable::exists('document_core_versions')) {
                $stored = $this->storage->storeUploadedFile($file, $document->uuid);

                $version = DocumentVersion::create(array_merge($stored, [
                    'document_id' => $document->id,
                    'version_number' => 1,
                    'label' => 'Initial upload',
                    'processing_status' => 'uploaded',
                    'created_by' => $userId,
                ]));

                $document->update([
                    'current_version_id' => $version->id,
                    'mime_type' => $stored['mime_type'],
                    'extension' => $stored['extension'],
                    'size_bytes' => $stored['size_bytes'],
                    'checksum_algorithm' => $stored['checksum_algorithm'],
                    'checksum' => $stored['checksum'],
                    'has_preview' => $this->preview->canPreview($stored['mime_type']),
                    'search_text' => trim($document->title . ' ' . (string) $document->description),
                ]);
            }

            if (DocumentTable::exists('document_core_tags') && DocumentTable::exists('document_core_document_tags')) {
                $tagIds = $this->resolveTagIds($data->tagIds, $data->tagNames, $userId);

                if (!empty($tagIds)) {
                    $document->tags()->sync($tagIds);
                }
            }

            $this->syncMetadataRows($document, $document->metadata ?? []);

            $this->audit->activity($document->id, 'document.created', [
                'has_file' => (bool) $file,
                'pipeline' => config('documentmanager.pipeline', []),
            ], $userId);

            return $document;
        });

        if ($document->has_file && (bool) config('documentmanager.process_after_upload', true)) {
            if ((bool) config('documentmanager.process_after_upload_sync', true)) {
                ProcessDocumentPipeline::dispatchSync($document->id, $document->current_version_id);
            } else {
                ProcessDocumentPipeline::dispatch($document->id, $document->current_version_id);
            }

            $document->refresh();
        }

        return $document;
    }

    public function update(Document $document, array $data, ?int $userId = null): Document
    {
        if ($document->is_immutable || $document->is_locked) {
            throw new DocumentManagerException('Document is locked or immutable.');
        }

        $document->fill([
            'title' => trim((string) ($data['title'] ?? $document->title)),
            'description' => $data['description'] ?? $document->description,
            'workspace_id' => $data['workspace_id'] ?? $document->workspace_id,
            'folder_id' => $data['folder_id'] ?? $document->folder_id,
            'category_id' => $data['category_id'] ?? $document->category_id,
            'document_type' => $data['document_type'] ?? $document->document_type,
            'metadata' => $this->normalizeMetadata($data['metadata'] ?? $document->metadata ?? []),
            'updated_by' => $userId,
        ]);

        $document->slug = Str::slug($document->title) ?: $document->slug;
        $document->search_text = trim($document->title . ' ' . (string) $document->description);
        $document->save();

        if (DocumentTable::exists('document_core_tags') && DocumentTable::exists('document_core_document_tags')) {
            $document->tags()->sync($this->resolveTagIds(
                array_values(array_filter(array_map('intval', (array) ($data['tag_ids'] ?? [])))),
                $this->parseTagNames((string) ($data['tag_names'] ?? '')),
                $userId
            ));
        }

        $this->syncMetadataRows($document, $document->metadata ?? []);

        $this->audit->activity($document->id, 'document.updated', [], $userId);

        return $document;
    }

    private function resolveTagIds(array $tagIds, array $tagNames, ?int $userId = null): array
    {
        if (!DocumentTable::exists('document_core_tags')) {
            return $tagIds;
        }

        foreach ($tagNames as $name) {
            $slug = Str::slug($name) ?: Str::random(10);
            $existing = DB::table('document_core_tags')
                ->where(function ($query) use ($name, $slug) {
                    $query->where('name', $name)->orWhere('slug', $slug);
                })
                ->first();

            if ($existing) {
                $tagIds[] = (int) $existing->id;
                continue;
            }

            $tagIds[] = (int) DB::table('document_core_tags')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'name' => $name,
                'slug' => $slug,
                'type' => 'manual',
                'color' => '#60a5fa',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return array_values(array_unique(array_filter(array_map('intval', $tagIds))));
    }

    private function parseTagNames(string $value): array
    {
        $tags = preg_split('/[,;\r\n]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter(array_map(
            fn ($tag) => trim($tag),
            $tags
        ))));
    }

    private function normalizeMetadata(array $metadata): array
    {
        return array_filter([
            'document_value' => $metadata['document_value'] ?? null,
            'currency' => $metadata['currency'] ?? null,
            'payment_status' => $metadata['payment_status'] ?? null,
            'paid_at' => $metadata['paid_at'] ?? null,
            'paid_by' => $metadata['paid_by'] ?? null,
            'payment_method' => $metadata['payment_method'] ?? null,
            'payment_reference' => $metadata['payment_reference'] ?? null,
            'operational_notes' => $metadata['operational_notes'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function syncMetadataRows(Document $document, array $metadata): void
    {
        if (!DocumentTable::exists('document_core_metadata')) {
            return;
        }

        $managedKeys = [
            'document_value',
            'currency',
            'payment_status',
            'paid_at',
            'paid_by',
            'payment_method',
            'payment_reference',
            'operational_notes',
        ];

        DB::table('document_core_metadata')
            ->where('document_id', $document->id)
            ->whereIn('key', $managedKeys)
            ->when(!empty($metadata), fn ($query) => $query->whereNotIn('key', array_keys($metadata)))
            ->delete();

        foreach ($metadata as $key => $value) {
            DB::table('document_core_metadata')->updateOrInsert(
                ['document_id' => $document->id, 'key' => $key],
                [
                    'value' => is_scalar($value) ? (string) $value : json_encode($value),
                    'value_type' => is_numeric($value) ? 'number' : 'string',
                    'source' => 'manual',
                    'is_searchable' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
