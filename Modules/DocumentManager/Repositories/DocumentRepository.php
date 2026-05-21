<?php

namespace Modules\DocumentManager\Repositories;

use Illuminate\Support\Facades\DB;
use Modules\DocumentManager\Support\DocumentTable;

class DocumentRepository
{
    public function paginate(array $filters = [], int $perPage = 25)
    {
        if (!DocumentTable::exists('document_core_documents')) {
            return DocumentTable::emptyPaginator($perPage);
        }

        $select = [
                'd.id',
                'd.uuid',
                'd.title',
                'd.document_type',
                'd.status',
                'd.workflow_state',
                'd.mime_type',
                'd.size_bytes',
                'd.has_file',
                'd.has_ocr',
                'd.has_preview',
                'd.has_embeddings',
                'd.created_at',
        ];

        $query = DB::table('document_core_documents as d');

        if (DocumentTable::exists('document_core_workspaces')) {
            $query->leftJoin('document_core_workspaces as w', 'w.id', '=', 'd.workspace_id');
            $select[] = 'w.name as workspace_name';
        } else {
            $select[] = DB::raw('NULL as workspace_name');
        }

        if (DocumentTable::exists('document_core_categories')) {
            $query->leftJoin('document_core_categories as c', 'c.id', '=', 'd.category_id');
            $select[] = 'c.name as category_name';
        } else {
            $select[] = DB::raw('NULL as category_name');
        }

        $query->select($select)->whereNull('d.deleted_at');

        if (!empty($filters['q'])) {
            $term = '%' . trim($filters['q']) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('d.title', 'like', $term)
                    ->orWhere('d.description', 'like', $term)
                    ->orWhere('d.search_text', 'like', $term)
                    ->orWhere('d.checksum', 'like', $term);
            });
        }

        if (!empty($filters['workspace_id'])) {
            $query->where('d.workspace_id', (int) $filters['workspace_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('d.status', $filters['status']);
        }

        return $query->orderByDesc('d.id')->paginate($perPage)->withQueryString();
    }

    public function latest(int $limit = 8)
    {
        if (!DocumentTable::exists('document_core_documents')) {
            return collect();
        }

        return DB::table('document_core_documents')
            ->select('id', 'title', 'document_type', 'status', 'workflow_state', 'created_at')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
