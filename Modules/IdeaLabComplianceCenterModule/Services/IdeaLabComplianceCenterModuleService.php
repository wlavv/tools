<?php

namespace Modules\IdeaLabComplianceCenterModule\Services;

class IdeaLabComplianceCenterModuleService
{
    public function health(): array
    {
        return [
            'status' => 'draft',
            'message' => 'Generated package awaiting human review.',
        ];
    }
}