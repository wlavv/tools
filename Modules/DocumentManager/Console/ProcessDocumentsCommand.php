<?php

namespace Modules\DocumentManager\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Modules\DocumentManager\Jobs\ProcessDocumentPipeline;
use Modules\DocumentManager\Support\DocumentTable;

class ProcessDocumentsCommand extends Command
{
    protected $signature = 'document-manager:process {documentId? : Document id} {--all : Process all documents with files} {--pending : Process only documents missing OCR or summaries}';

    protected $description = 'Run DocumentManager OCR, summary, analysis and embedding pipeline.';

    public function handle(): int
    {
        if (!DocumentTable::exists('document_core_documents')) {
            $this->error('document_core_documents table missing.');
            return self::FAILURE;
        }

        $documentId = $this->argument('documentId');

        if ($documentId) {
            $this->processOne((int) $documentId);
            return self::SUCCESS;
        }

        if (!$this->option('all') && !$this->option('pending')) {
            $this->warn('Use a document id, --all or --pending.');
            return self::INVALID;
        }

        $query = DB::table('document_core_documents')
            ->select(['id', 'current_version_id'])
            ->where('has_file', true)
            ->whereNull('deleted_at')
            ->orderBy('id');

        if ($this->option('pending')) {
            $query->where(function ($pending) {
                $pending->where('has_ocr', false)
                    ->orWhere('has_embeddings', false);
            });
        }

        $count = 0;

        $query->chunk(100, function ($documents) use (&$count) {
            foreach ($documents as $document) {
                $this->processOne((int) $document->id, $document->current_version_id ? (int) $document->current_version_id : null);
                $count++;
            }
        });

        $this->info('Processed documents: ' . $count);

        return self::SUCCESS;
    }

    private function processOne(int $documentId, ?int $versionId = null): void
    {
        if ($versionId === null && DocumentTable::exists('document_core_documents')) {
            $versionId = DB::table('document_core_documents')
                ->where('id', $documentId)
                ->value('current_version_id');
        }

        ProcessDocumentPipeline::dispatchSync($documentId, $versionId ? (int) $versionId : null);

        $this->line('Processed document #' . $documentId);
    }
}
