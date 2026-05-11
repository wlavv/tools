<?php

namespace Modules\DocumentManager\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\DocumentManager\Services\AiService;

class AnalyzeDocumentWithAi implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $documentId, public string $operation = 'analysis')
    {
        $this->onQueue(config('documentmanager.queues.ai', 'dms_ai'));
    }

    public function handle(AiService $ai): void
    {
        if ($this->operation === 'summary') {
            $ai->summarize($this->documentId);
            return;
        }

        $ai->analyze($this->documentId);
    }
}
