<?php

namespace Modules\AIConsensus\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AIConsensus\Services\AIConsensusRunService;

class BuildAIConsensusResultJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 1800;
    public int $tries = 2;

    public function __construct(public int $runId)
    {
        $this->onQueue(config('ai_consensus.queue.name', 'ai-consensus'));
    }

    public function handle(AIConsensusRunService $runService): void
    {
        $runService->process($this->runId);
    }
}
