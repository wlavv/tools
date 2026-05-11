<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\DocumentManager\Models\Document;
use Modules\DocumentManager\Support\DocumentLogger;
use Modules\DocumentManager\Support\DocumentTable;

class WorkflowService
{
    public function transition(Document $document, string $state, ?int $userId = null, ?string $reason = null): Document
    {
        $allowed = config('documentmanager.workflow_states', []);

        if (!in_array($state, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid document workflow state.');
        }

        $from = $document->workflow_state;
        $document->workflow_state = $state;
        $document->status = $state === 'approved' ? 'approved' : $document->status;
        $document->updated_by = $userId;
        $document->save();

        if (DocumentTable::exists('document_workflow_states')) {
            try {
                DB::table('document_workflow_states')->insert([
                    'uuid' => (string) Str::uuid(),
                    'document_id' => $document->id,
                    'from_state' => $from,
                    'to_state' => $state,
                    'reason' => $reason,
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {
                DocumentLogger::exception($e, ['document_id' => $document->id, 'state' => $state]);
            }
        }

        return $document;
    }

    public function stats(): array
    {
        $stats = [];

        foreach (config('documentmanager.workflow_states', []) as $state) {
            $stats[$state] = DocumentTable::count('document_core_documents', function ($query) use ($state) {
                $query->where('workflow_state', $state);
            });
        }

        return $stats;
    }
}
