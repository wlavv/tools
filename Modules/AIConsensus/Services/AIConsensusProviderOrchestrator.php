<?php

namespace Modules\AIConsensus\Services;

use Modules\AIConsensus\Models\AIConsensusProvider;
use Modules\AIConsensus\Models\AIConsensusProviderResponse;
use Modules\AIConsensus\Models\AIConsensusRun;

class AIConsensusProviderOrchestrator
{
    public function __construct(protected AIConsensusPromptBuilder $promptBuilder)
    {
    }

    public function execute(AIConsensusRun $run): array
    {
        $providers = AIConsensusProvider::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->get();

        if ($providers->isEmpty()) {
            $providers = collect([(object) ['id' => null, 'provider_key' => 'internal_rules_engine', 'driver' => 'internal_rules_engine', 'weight' => 1]]);
        }

        $prompt = $this->promptBuilder->build($run);
        $responses = [];

        foreach ($providers as $provider) {
            $start = microtime(true);
            try {
                $content = $this->executeProvider($provider, $run, $prompt);
                $response = AIConsensusProviderResponse::query()->create([
                    'run_id' => $run->id,
                    'provider_id' => $provider->id ?? null,
                    'status' => 'completed',
                    'input_payload' => ['prompt' => $prompt],
                    'raw_response' => $content,
                    'normalized_response' => json_decode($content, true) ?: ['content' => $content],
                    'score' => 80,
                    'latency_ms' => (int) round((microtime(true) - $start) * 1000),
                ]);
                $responses[] = $response;
            } catch (\Throwable $e) {
                $responses[] = AIConsensusProviderResponse::query()->create([
                    'run_id' => $run->id,
                    'provider_id' => $provider->id ?? null,
                    'status' => 'failed',
                    'input_payload' => ['prompt' => $prompt],
                    'error_message' => $e->getMessage(),
                    'latency_ms' => (int) round((microtime(true) - $start) * 1000),
                ]);
            }

            if (data_get($run->options, 'consensus_mode', 'single_provider') === 'single_provider') {
                break;
            }
        }

        return $responses;
    }

    protected function executeProvider(object $provider, AIConsensusRun $run, string $prompt): string
    {
        if (($provider->driver ?? null) !== 'internal_rules_engine') {
            return $this->mockExternalProvider($provider, $run);
        }

        return json_encode([
            'summary' => 'AI Consensus central run prepared and processed by the internal rules engine.',
            'source' => [
                'module' => $run->source_module,
                'type' => $run->source_type,
                'id' => $run->source_id,
            ],
            'output_type' => $run->output_type,
            'recommendations' => [
                'Review the generated structure with a human owner.',
                'Use provider-backed execution when live AI credentials are enabled.',
            ],
            'risks' => [
                'This first phase uses an internal provider stub unless real providers are activated.',
            ],
            'next_actions' => [
                'Refine the template payload.',
                'Approve the output before critical automation.',
            ],
            'input_digest' => array_slice($run->input_payload ?? [], 0, 12, true),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    protected function mockExternalProvider(object $provider, AIConsensusRun $run): string
    {
        return json_encode([
            'summary' => 'Provider [' . ($provider->provider_key ?? $provider->driver) . '] is configured structurally but not executed in this phase.',
            'recommendations' => ['Enable a concrete provider driver before production AI execution.'],
            'risks' => ['No external request was sent.'],
            'next_actions' => ['Implement provider driver and credentials validation.'],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
