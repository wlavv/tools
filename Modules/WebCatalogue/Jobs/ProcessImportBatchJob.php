<?php

namespace Modules\WebCatalogue\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\WebCatalogue\Http\Controllers\Imports\ImportCenterController;
use Modules\WebCatalogue\Models\ImportBatch;

class ProcessImportBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public function __construct(public int $batchId)
    {
    }

    public function handle(ImportCenterController $processor): void
    {
        $batch = ImportBatch::query()->find($this->batchId);

        if (!$batch) {
            return;
        }

        $processor->processBatch($batch);
    }
}
