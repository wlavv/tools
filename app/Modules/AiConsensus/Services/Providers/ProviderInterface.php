<?php

namespace App\Modules\AiConsensus\Services\Providers;

interface ProviderInterface
{
    public function name(): string;

    /**
     * @return array{output:string, model:string, tokens_in:?int, tokens_out:?int, cost_usd:?float, meta:array}
     */
    public function generate(string $system, string $userPrompt, array $options = []): array;
}