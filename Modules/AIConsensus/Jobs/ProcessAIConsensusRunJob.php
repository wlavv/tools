<?php

namespace Modules\AIConsensus\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AIConsensus\Services\AIConsensusRunService;
use Modules\AIConsensus\Services\AIConsensusService;

class ProcessAIConsensusRunJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800;
    public int $tries = 3;

    public function __construct(public int $runId, public bool $central = false)
    {
        $this->onQueue(config('ai_consensus.queue.name', 'ai-consensus'));
    }

    public function handle(AIConsensusService $service, AIConsensusRunService $runService): void
    {
        try {
            if ($this->central) {
                $runService->process($this->runId);
                return;
            }

            $service->processQueuedRun($this->runId);
        } catch (\Throwable $e) {
            \Log::error('AI Consensus job failed', [
                'run_id' => $this->runId,
                'central' => $this->central,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
