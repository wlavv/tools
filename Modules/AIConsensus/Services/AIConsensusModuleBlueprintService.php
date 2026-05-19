<?php

namespace Modules\AIConsensus\Services;

class AIConsensusModuleBlueprintService
{
    public function lsgRules(): array
    {
        return config('ai-consensus-lsg.rules', []);
    }
}
