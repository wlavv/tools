<?php

namespace Modules\DocumentManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DocumentManager\Services\AiService;
use Modules\DocumentManager\Services\EmbeddingService;
use Modules\DocumentManager\Services\OcrService;

class ProcessDocumentPipeline implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $documentId, public ?int $versionId = null)
    {
        $this->onQueue(config('documentmanager.queues.ai', 'dms_ai'));
    }

    public function handle(OcrService $ocr, AiService $ai, EmbeddingService $embeddings): void
    {
        $ocrResult = $ocr->process($this->documentId, $this->versionId);
        $text = $ocrResult['text'] ?? null;

        $ai->summarize($this->documentId, $text);
        $ai->analyze($this->documentId, $text);
        $embeddings->process($this->documentId, $this->versionId);
    }
}
