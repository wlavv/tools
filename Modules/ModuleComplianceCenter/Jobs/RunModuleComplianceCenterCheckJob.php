<?php

namespace Modules\ModuleComplianceCenter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\ModuleComplianceCenter\Models\ComplianceRun;
use Modules\ModuleComplianceCenter\Services\ComplianceRunService;

class RunModuleComplianceCenterCheckJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $runId)
    {
    }

    public function handle(ComplianceRunService $service): void
    {
        $run = ComplianceRun::findOrFail($this->runId);
        $service->execute($run);
    }

    public function failed(\Throwable $exception): void
    {
        ComplianceRun::whereKey($this->runId)->update([
            'status' => 'failed',
            'final_status' => 'manual_review_required',
            'error_message' => $exception->getMessage(),
            'finished_at' => now(),
        ]);
    }
}
