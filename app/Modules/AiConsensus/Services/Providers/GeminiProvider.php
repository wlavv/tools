<?php

namespace App\Modules\AiConsensus\Services\Providers;

use Illuminate\Support\Facades\Http;

class GeminiProvider implements ProviderInterface
{
    public function name(): string { return 'gemini'; }

    public function generate(string $system, string $userPrompt, array $options = []): array
    {
        $apiKey = '';
        if (!$apiKey) throw new \RuntimeException('Missing GEMINI_API_KEY');

        $model = $options['model'] ?? config('aiconsensus.models.gemini', 'gemini-2.5-pro');
        $timeout = $options['timeout'] ?? config('aiconsensus.timeouts.draft_seconds', 90);

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $payload = [
            'system_instruction' => ['parts' => [['text' => $system]]],
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $userPrompt]]],
            ],
        ];

        $t0 = microtime(true);
        $res = Http::timeout($timeout)->acceptJson()->post($url, $payload);
        $latencyMs = (int) round((microtime(true) - $t0) * 1000);

        if (!$res->ok()) {
            throw new \RuntimeException('Gemini error: ' . $res->status() . ' ' . $res->body());
        }

        $json = $res->json();

        $usage = $json['usageMetadata'] ?? [];
        $tokensIn = $usage['promptTokenCount'] ?? null;
        $tokensOut = $usage['candidatesTokenCount'] ?? null;  

        $output = '';

        $candidates = $json['candidates'] ?? [];
        if (!empty($candidates[0]['content']['parts'])) {
            foreach ($candidates[0]['content']['parts'] as $p) {
                $output .= ($p['text'] ?? '');
            }
        }

        return [
            'output' => $output,
            'model' => $model,
            'tokens_in' => null,
            'tokens_out' => null,
            'cost_usd' => null,
            'meta' => [
                'latency_ms' => $latencyMs,
            ],
            'tokens_in' => $tokensIn,
            'tokens_out' => $tokensOut,
        ];
    }
}