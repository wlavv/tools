<?php

namespace Modules\ModuleComplianceCenter\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\ModuleComplianceCenter\Models\ComplianceRun;
use Modules\ModuleComplianceCenter\Services\ComplianceReportService;

class GenerateComplianceReportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly int $runId)
    {
    }

    public function handle(ComplianceReportService $reports): void
    {
        $reports->generate(ComplianceRun::findOrFail($this->runId));
    }
}
