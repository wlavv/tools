<?php

namespace Modules\DocumentManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DocumentManager\Services\EmbeddingService;

class GenerateDocumentEmbedding implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $documentId, public ?int $versionId = null)
    {
        $this->onQueue(config('documentmanager.queues.embeddings', 'dms_embeddings'));
    }

    public function handle(EmbeddingService $embeddings): void
    {
        $embeddings->process($this->documentId, $this->versionId);
    }
}
