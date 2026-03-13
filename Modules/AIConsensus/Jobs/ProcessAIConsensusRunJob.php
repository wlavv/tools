<?php

namespace Modules\AIConsensus\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AIConsensus\Services\AIConsensusService;

class ProcessAIConsensusRunJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800;
    public int $tries = 3;

    public function __construct(public int $runId)
    {
        $this->onQueue('ai-consensus');
    }

    public function handle(AIConsensusService $service): void
    {
        try {
            $service->processQueuedRun($this->runId);
        } catch (\Throwable $e) {
            \Log::error('AI Consensus job failed', [
                'run_id' => $this->runId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}