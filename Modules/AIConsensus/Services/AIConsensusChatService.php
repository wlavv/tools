<?php

namespace Modules\AIConsensus\Services;

use Modules\AIConsensus\Models\AIConsensusMessage;
use Modules\AIConsensus\Models\AIConsensusRun;

class AIConsensusChatService
{
    public function addMessage(AIConsensusRun $run, string $role, string $message, array $payload = [], ?int $userId = null): AIConsensusMessage
    {
        return $run->messages()->create([
            'role' => $role,
            'message' => $message,
            'payload' => $payload ?: null,
            'created_by' => $userId,
        ]);
    }
}
