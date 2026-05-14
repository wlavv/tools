<?php

namespace Modules\IntegrationHealth\Console;

use Illuminate\Console\Command;
use Modules\IntegrationHealth\Services\IntegrationHealthService;

class EvaluateIntegrationHealthCommand extends Command
{
    protected $signature = 'integration-health:evaluate';
    protected $description = 'Evaluate integration heartbeats and update health statuses.';

    public function handle(IntegrationHealthService $healthService): int
    {
        $healthService->bootstrapDefaultServices();
        $healthService->evaluateHeartbeats();
        $this->info('Integration health evaluated.');

        return self::SUCCESS;
    }
}
