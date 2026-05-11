<?php

namespace Modules\DocumentManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DocumentManager\Services\OcrService;

class RunDocumentOcr implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $documentId, public ?int $versionId = null)
    {
        $this->onQueue(config('documentmanager.queues.ocr', 'dms_ocr'));
    }

    public function handle(OcrService $ocr): void
    {
        $ocr->process($this->documentId, $this->versionId);
    }
}
