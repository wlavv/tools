<?php

namespace Modules\AIConsensus\Services;

use Illuminate\Support\Collection;
use Modules\AIConsensus\Models\AIConsensusRun;

class AIConsensusEngine
{
    public function build(AIConsensusRun $run, array|Collection $responses): string
    {
        $collection = collect($responses)->filter(fn ($response) => ($response->status ?? null) === 'completed');
        $first = $collection->first();

        if (!$first) {
            throw new \RuntimeException('No successful provider responses were available.');
        }

        $openAiFinal = $collection->first(function ($response) {
            return in_array($response->provider?->provider_key, ['openai_gpt', 'openai'], true)
                || $response->provider?->driver === 'openai';
        });

        if ($openAiFinal?->raw_response) {
            return (string) $openAiFinal->raw_response;
        }

        if (in_array(data_get($run->options, 'consensus_mode'), ['weighted_consensus', 'architect_reviewer', 'lsg_validator'], true)) {
            return json_encode([
                'summary' => 'Consensus built from ' . $collection->count() . ' provider response(s).',
                'provider_outputs' => $collection->map(fn ($item) => $item->normalized_response ?: $item->raw_response)->values()->all(),
                'recommendations' => ['Review consolidated response before using it downstream.'],
                'risks' => [],
                'next_actions' => ['Approve or request refinement.'],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        }

        return (string) $first->raw_response;
    }
}
