<?php

namespace Modules\AIConsensus\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AIConsensus\Models\AIConsensusRun;
use Modules\AIConsensus\Services\AIConsensusProviderOrchestrator;
use Modules\AIConsensus\Services\AIConsensusRunService;

class ExecuteAIProviderRequestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800;
    public int $tries = 2;

    public function __construct(public int $runId)
    {
        $this->onQueue('ai-consensus');
    }

    public function handle(AIConsensusProviderOrchestrator $orchestrator, AIConsensusRunService $runService): void
    {
        $run = AIConsensusRun::query()->with('template')->findOrFail($this->runId);
        $orchestrator->execute($run);

        $runService->log($run, 'info', 'providers.executed', 'Provider requests executed.');
    }
}
