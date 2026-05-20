<?php

namespace Modules\ModuleComplianceCenter\Services;

use Modules\ModuleComplianceCenter\Models\ComplianceRun;

class AIConsensusComplianceBridge
{
    public function sendRunToAIConsensus(ComplianceRun $run): ?int
    {
        $gatewayClass = '\\Modules\\AIConsensus\\Services\\AIConsensusGateway';

        if (!class_exists($gatewayClass)) {
            return null;
        }

        $payload = [
            'template' => 'modules.lsg_validation_report_analysis',
            'module_name' => $run->module_name,
            'scores' => $run->only(['structure_score', 'design_score', 'security_score', 'integration_score', 'health_score', 'final_score']),
            'findings' => $run->results()->get()->toArray(),
            'report' => optional($run->report)->report_payload,
            'source_type' => $run->source_type,
            'source_id' => $run->source_id,
        ];

        $response = app($gatewayClass)->run($payload);

        return is_numeric($response) ? (int) $response : data_get($response, 'id');
    }
}
