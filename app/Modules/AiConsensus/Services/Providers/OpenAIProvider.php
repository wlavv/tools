<?php

namespace App\Modules\AiConsensus\Services\Providers;

use Illuminate\Support\Facades\Http;

class OpenAIProvider implements ProviderInterface
{
    public function name(): string { return 'openai'; }

    public function generate(string $system, string $userPrompt, array $options = []): array
    {
        $apiKey = '';
        if (!$apiKey) throw new \RuntimeException('Missing OPENAI_API_KEY');

        $model = $options['model'] ?? config('aiconsensus.models.openai', 'gpt-5');
        $timeout = $options['timeout'] ?? config('aiconsensus.timeouts.integrator_seconds', 120);

        $payload = [
            'model' => $model,
            'input' => [
                ['role' => 'system', 'content' => [['type' => 'input_text', 'text' => $system]]],
                ['role' => 'user',   'content' => [['type' => 'input_text', 'text' => $userPrompt]]],
            ],
        ];

        $t0 = microtime(true);
        $res = Http::timeout($timeout)
            ->withToken($apiKey)
            ->acceptJson()
            ->post('https://api.openai.com/v1/responses', $payload);

        $latencyMs = (int) round((microtime(true) - $t0) * 1000);

        if (!$res->ok()) {
            throw new \RuntimeException('OpenAI error: ' . $res->status() . ' ' . $res->body());
        }

        $json = $res->json();

        $output = '';
        foreach (($json['output'] ?? []) as $out) {
            if (($out['type'] ?? '') !== 'message') continue;
            foreach (($out['content'] ?? []) as $c) {
                if (($c['type'] ?? '') === 'output_text') $output .= ($c['text'] ?? '');
            }
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
                'response_id' => $json['id'] ?? null,
            ],
        ];
    }
}