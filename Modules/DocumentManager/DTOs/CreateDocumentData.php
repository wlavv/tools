<?php

namespace Modules\DocumentManager\DTOs;

class CreateDocumentData
{
    public string $title;
    public ?string $description;
    public ?int $workspaceId;
    public ?int $folderId;
    public ?int $categoryId;
    public ?string $documentType;
    public ?string $sourceModule;
    public ?string $sourceType;
    public ?int $sourceId;
    public array $tagIds;
    public array $metadata;

    public function __construct(array $data)
    {
        $this->title = trim((string) ($data['title'] ?? ''));
        $this->description = $data['description'] ?? null;
        $this->workspaceId = isset($data['workspace_id']) ? (int) $data['workspace_id'] : null;
        $this->folderId = isset($data['folder_id']) ? (int) $data['folder_id'] : null;
        $this->categoryId = isset($data['category_id']) ? (int) $data['category_id'] : null;
        $this->documentType = $data['document_type'] ?? null;
        $this->sourceModule = $data['source_module'] ?? null;
        $this->sourceType = $data['source_type'] ?? null;
        $this->sourceId = isset($data['source_id']) ? (int) $data['source_id'] : null;
        $this->tagIds = array_values(array_filter(array_map('intval', (array) ($data['tag_ids'] ?? []))));
        $this->metadata = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
    }

    public function toDocumentArray(?int $userId = null): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'workspace_id' => $this->workspaceId,
            'folder_id' => $this->folderId,
            'category_id' => $this->categoryId,
            'document_type' => $this->documentType,
            'source_module' => $this->sourceModule,
            'source_type' => $this->sourceType,
            'source_id' => $this->sourceId,
            'metadata' => $this->metadata,
            'created_by' => $userId,
            'updated_by' => $userId,
        ];
    }
}
