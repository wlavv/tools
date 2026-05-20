<?php

namespace Modules\ModuleComplianceCenter\Services;

use Modules\ModuleComplianceCenter\Models\ComplianceRun;

class ComplianceApprovalService
{
    public function approve(ComplianceRun $run, ?int $userId = null): ComplianceRun
    {
        $run->update(['status' => 'approved', 'final_status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);

        return $run->fresh();
    }

    public function reject(ComplianceRun $run, string $reason, ?int $userId = null): ComplianceRun
    {
        $run->update(['status' => 'rejected', 'final_status' => 'rejected', 'rejected_by' => $userId, 'rejected_at' => now(), 'rejection_reason' => $reason]);

        return $run->fresh();
    }

    public function requestChanges(ComplianceRun $run, ?string $reason = null, ?int $userId = null): ComplianceRun
    {
        $run->update(['status' => 'changes_required', 'final_status' => 'changes_required', 'rejected_by' => $userId, 'rejected_at' => now(), 'rejection_reason' => $reason]);

        return $run->fresh();
    }
}
