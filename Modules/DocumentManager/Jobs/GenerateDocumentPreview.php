<?php

namespace Modules\DocumentManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DocumentManager\Services\AuditService;

class GenerateDocumentPreview implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $documentId)
    {
        $this->onQueue(config('documentmanager.queues.preview', 'dms_preview'));
    }

    public function handle(AuditService $audit): void
    {
        $audit->activity($this->documentId, 'preview.queued');
    }
}
