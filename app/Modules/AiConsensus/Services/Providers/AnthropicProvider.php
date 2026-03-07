<?php

namespace App\Modules\AiConsensus\Services\Providers;

use Illuminate\Support\Facades\Http;

class AnthropicProvider implements ProviderInterface
{
    public function name(): string { return 'anthropic'; }

    public function generate(string $system, string $userPrompt, array $options = []): array
    {
        $apiKey = '';
        if (!$apiKey) throw new \RuntimeException('Missing ANTHROPIC_API_KEY');

        $model = $options['model'] ?? config('aiconsensus.models.anthropic', 'claude-sonnet-4');
        $timeout = $options['timeout'] ?? config('aiconsensus.timeouts.draft_seconds', 90);
        $maxTokens = $options['max_tokens'] ?? 2500;

        $payload = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'system' => $system,
            'messages' => [
                ['role' => 'user', 'content' => $userPrompt],
            ],
        ];

        $t0 = microtime(true);
        $res = Http::timeout($timeout)
            ->withHeaders([
                'x-api-key' => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post('https://api.anthropic.com/v1/messages', $payload);

        $latencyMs = (int) round((microtime(true) - $t0) * 1000);

        if (!$res->ok()) {
            throw new \RuntimeException('Anthropic error: ' . $res->status() . ' ' . $res->body());
        }

        $json = $res->json();
        $output = '';
        foreach (($json['content'] ?? []) as $c) {
            if (($c['type'] ?? '') === 'text') $output .= ($c['text'] ?? '');
        }

        $usage = $json['usage'] ?? [];
        return [
            'output' => $output,
            'model' => $model,
            'tokens_in' => $usage['input_tokens'] ?? null,
            'tokens_out' => $usage['output_tokens'] ?? null,
            'cost_usd' => null,
            'meta' => [
                'latency_ms' => $latencyMs,
                'id' => $json['id'] ?? null,
            ],
        ];
    }
}