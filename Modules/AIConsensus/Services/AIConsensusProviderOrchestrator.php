<?php

namespace Modules\AIConsensus\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\AIConsensus\Models\AIConsensusProvider;
use Modules\AIConsensus\Models\AIConsensusProviderResponse;
use Modules\AIConsensus\Models\AIConsensusRun;

class AIConsensusProviderOrchestrator
{
    protected array $providerMap = [
        'anthropic' => 'anthropic_claude',
        'gemini' => 'google_gemini',
        'openai' => 'openai_gpt',
    ];

    public function __construct(
        protected AIConsensusPromptBuilder $promptBuilder,
        protected AIConsensusService $legacyService,
    ) {
    }

    public function execute(AIConsensusRun $run): array
    {
        $prompt = $this->promptBuilder->build($run);
        $responses = [];

        $analysisProviders = $this->analysisProviders($run);
        foreach ($analysisProviders as $provider) {
            $responses[] = $this->executeRealProvider($run, $provider, $prompt);
        }

        if ($this->shouldRunOpenAIFinal($run)) {
            $responses[] = $this->executeRealProvider(
                $run,
                'openai',
                $this->buildOpenAIIntegrationPrompt($run, $prompt, collect($responses))
            );
        }

        $hasCompleted = collect($responses)->contains(fn ($response) => ($response->status ?? null) === 'completed');
        if (!$hasCompleted && $this->shouldUseInternalFallback($run)) {
            $responses[] = $this->executeInternalRulesEngine($run, $prompt);
        }

        return $responses;
    }

    protected function analysisProviders(AIConsensusRun $run): array
    {
        $providers = [];

        if ((bool) data_get($run->options, 'run_claude', true)) {
            $providers[] = 'anthropic';
        }

        if ((bool) data_get($run->options, 'run_gemini', true)) {
            $providers[] = 'gemini';
        }

        if (data_get($run->options, 'consensus_mode') === 'single_provider') {
            return array_slice($providers, 0, 1);
        }

        return $providers;
    }

    protected function shouldRunOpenAIFinal(AIConsensusRun $run): bool
    {
        return (bool) data_get($run->options, 'run_openai_final', true);
    }

    protected function shouldUseInternalFallback(AIConsensusRun $run): bool
    {
        return (bool) data_get($run->options, 'allow_internal_fallback', true);
    }

    protected function executeRealProvider(AIConsensusRun $run, string $provider, string $prompt): AIConsensusProviderResponse
    {
        $providerRecord = $this->providerRecord($provider);
        $start = microtime(true);

        try {
            if (!$this->legacyService->hasActiveProviderCredential($provider)) {
                throw new \RuntimeException("Provider [$provider] sem credencial ativa.");
            }

            $result = $this->legacyService->executeProviderPrompt($provider, $prompt, $run->options ?? []);
            $content = (string) ($result['text'] ?? '');

            return AIConsensusProviderResponse::query()->create([
                'run_id' => $run->id,
                'provider_id' => $providerRecord?->id,
                'status' => 'completed',
                'input_payload' => [
                    'provider' => $provider,
                    'prompt' => $prompt,
                ],
                'raw_response' => $content,
                'normalized_response' => $this->normalizeProviderContent($content),
                'score' => $provider === 'openai' ? 95 : 85,
                'cost_estimate' => $result['cost'] ?? 0,
                'tokens_input' => $result['tokens_in'] ?? null,
                'tokens_output' => $result['tokens_out'] ?? null,
                'latency_ms' => $result['latency_ms'] ?? (int) round((microtime(true) - $start) * 1000),
            ]);
        } catch (\Throwable $e) {
            return AIConsensusProviderResponse::query()->create([
                'run_id' => $run->id,
                'provider_id' => $providerRecord?->id,
                'status' => 'failed',
                'input_payload' => [
                    'provider' => $provider,
                    'prompt' => Str::limit($prompt, 12000),
                ],
                'error_message' => $e->getMessage(),
                'latency_ms' => (int) round((microtime(true) - $start) * 1000),
            ]);
        }
    }

    protected function executeInternalRulesEngine(AIConsensusRun $run, string $prompt): AIConsensusProviderResponse
    {
        $provider = AIConsensusProvider::query()->firstOrCreate(
            ['provider_key' => 'internal_rules_engine'],
            [
                'name' => 'Internal Rules Engine',
                'driver' => 'internal_rules_engine',
                'model' => 'rules-v1',
                'is_active' => true,
                'priority' => 1,
                'weight' => 1,
            ]
        );

        $content = json_encode([
            'summary' => 'AI Consensus run processed by the internal fallback because no live provider completed successfully.',
            'source' => [
                'module' => $run->source_module,
                'type' => $run->source_type,
                'id' => $run->source_id,
            ],
            'output_type' => $run->output_type,
            'recommendations' => [
                'Confirm that Claude, Gemini and OpenAI credentials are active.',
                'Confirm that the queue worker is listening to the configured queue.',
            ],
            'risks' => [
                'This fallback is not a live AI response.',
            ],
            'next_actions' => [
                'Review provider errors in this run.',
                'Reprocess after credentials/worker are fixed.',
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        return AIConsensusProviderResponse::query()->create([
            'run_id' => $run->id,
            'provider_id' => $provider->id,
            'status' => 'completed',
            'input_payload' => ['prompt' => $prompt],
            'raw_response' => $content,
            'normalized_response' => $this->normalizeProviderContent($content),
            'score' => 40,
            'cost_estimate' => 0,
            'latency_ms' => 0,
        ]);
    }

    protected function buildOpenAIIntegrationPrompt(AIConsensusRun $run, string $basePrompt, Collection $responses): string
    {
        $blocks = [
            "PEDIDO ORIGINAL:\n" . $basePrompt,
            "CONTEXTO DO RUN:\n" . json_encode([
                'source_module' => $run->source_module,
                'source_type' => $run->source_type,
                'source_id' => $run->source_id,
                'output_type' => $run->output_type,
                'options' => $run->options,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE),
        ];

        foreach ($responses->filter(fn ($response) => ($response->status ?? null) === 'completed') as $response) {
            $provider = $response->provider?->provider_key ?? 'provider';
            $blocks[] = strtoupper($provider) . ":\n" . ($response->raw_response ?: '[sem conteúdo]');
        }

        $blocks[] = "INSTRUÇÕES:\n"
            . "Produz uma resposta final consolidada e validada em português. "
            . "Integra convergências, divergências, riscos, lacunas, prioridades e próximos passos. "
            . "Se return_format=json, devolve JSON válido com summary, recommendations, risks e next_actions.";

        return implode("\n\n====================\n\n", $blocks);
    }

    protected function providerRecord(string $provider): ?AIConsensusProvider
    {
        $key = $this->providerMap[$provider] ?? $provider;

        return AIConsensusProvider::query()->firstOrCreate(
            ['provider_key' => $key],
            [
                'name' => Str::headline($provider),
                'driver' => $provider,
                'model' => config("ai_consensus.providers.{$provider}.default_model"),
                'is_active' => true,
                'priority' => $provider === 'openai' ? 90 : 50,
                'weight' => 1,
            ]
        );
    }

    protected function normalizeProviderContent(string $content): array
    {
        $json = json_decode($content, true);
        if (is_array($json)) {
            return $json;
        }

        return ['content' => $content];
    }
}
