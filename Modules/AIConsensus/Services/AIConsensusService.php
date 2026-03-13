<?php

namespace Modules\AIConsensus\Services;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\AIConsensus\Models\AIConsensus;
use Modules\AIConsensus\Models\AIFile;
use Modules\AIConsensus\Models\AIProviderCredential;
use Modules\AIConsensus\Models\AIRunResponse;
use ZipArchive;

class AIConsensusService
{
    public function getIndexData(array $filters = []): array
    {
        $query = AIConsensus::query()->withCount(['responses', 'files']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('prompt', 'like', '%' . $search . '%');
            });
        }

        $runs = $query->latest('id')->paginate(15)->withQueryString();

        return [
            'runs' => $runs,
            'stats' => $this->getStats(),
            'providerSettings' => $this->getProviderSettings(),
            'filters' => $filters,
        ];
    }

    public function getStats(): array
    {
        return [
            'total_runs' => AIConsensus::count(),
            'queued_runs' => AIConsensus::where('status', 'queued')->count(),
            'running_runs' => AIConsensus::whereIn('status', ['running', 'integrating'])->count(),
            'done_runs' => AIConsensus::where('status', 'done')->count(),
            'failed_runs' => AIConsensus::where('status', 'failed')->count(),
            'total_cost_usd' => (float) AIConsensus::sum('total_cost_estimate_usd'),
        ];
    }

    public function getProviderSettings(): EloquentCollection
    {
        return AIProviderCredential::query()
            ->orderBy('provider')
            ->get();
    }

    public function createRun(array $data, array $files = [], ?int $userId = null): AIConsensus
    {
        return DB::transaction(function () use ($data, $files, $userId) {
            $run = AIConsensus::create([
                'created_by' => $userId,
                'title' => $data['title'] ?? null,
                'template_key' => $data['template_key'] ?? config('ai_consensus.defaults.template_key', 'module_scaffold_v1'),
                'prompt' => $data['prompt'],
                'status' => 'queued',
                'options' => $data['options'] ?? [],
                'meta' => [
                    'source' => 'manual',
                ],
            ]);

            $this->storeUploadedFiles($run, $files);
            $this->processRun($run);

            return $run->fresh(['responses', 'files']);
        });
    }

    public function updateRun(AIConsensus $run, array $data, array $files = []): AIConsensus
    {
        $run->update([
            'title' => $data['title'] ?? $run->title,
            'template_key' => $data['template_key'] ?? $run->template_key,
            'prompt' => $data['prompt'] ?? $run->prompt,
            'status' => $data['status'] ?? $run->status,
            'options' => array_merge($run->options ?? [], $data['options'] ?? []),
        ]);

        if (!empty($files)) {
            $this->storeUploadedFiles($run, $files);
        }

        return $run->fresh(['responses', 'files']);
    }

    public function deleteRun(AIConsensus $run): void
    {
        foreach ($run->files as $file) {
            if ($file->stored_path) {
                Storage::disk(config('ai_consensus.storage.disk', 'local'))->delete($file->stored_path);
            }
        }

        $run->delete();
    }

    public function reprocessRun(AIConsensus $run): AIConsensus
    {
        $run->responses()->delete();
        $run->update([
            'status' => 'queued',
            'final_answer' => null,
            'final_provider' => null,
            'final_model' => null,
            'total_tokens_in' => null,
            'total_tokens_out' => null,
            'total_cost_estimate_usd' => null,
        ]);

        $this->processRun($run);

        return $run->fresh(['responses', 'files']);
    }

    public function saveProviderCredentials(array $data): AIProviderCredential
    {
        $provider = (string) $data['provider'];

        $credential = AIProviderCredential::firstOrNew(['provider' => $provider]);
        $credential->label = $data['label'] ?? Str::headline($provider);

        if (array_key_exists('base_url', $data)) {
            $credential->base_url = $data['base_url'];
        }

        if (array_key_exists('default_model', $data)) {
            $credential->default_model = $data['default_model'];
        }

        $credential->is_active = (bool) ($data['is_active'] ?? true);

        if (!empty($data['api_key'])) {
            $credential->api_key = $data['api_key'];
        }

        $credential->meta = array_merge($credential->meta ?? [], [
            'updated_manually' => true,
        ]);

        $credential->save();

        return $credential;
    }

    protected function processRun(AIConsensus $run): void
    {
        $run->update(['status' => 'running']);

        $includeFiles = (bool) data_get($run->options, 'include_files', true);
        $basePrompt = trim((string) $run->prompt);
        $filePromptBlock = $includeFiles ? $this->buildStoredFilesPromptBlock($run) : '';
        $effectivePrompt = trim($basePrompt . "\n\n" . $filePromptBlock);

        $providersToRun = [
            'anthropic' => (bool) data_get($run->options, 'run_claude', true),
            'gemini' => (bool) data_get($run->options, 'run_gemini', true),
        ];

        foreach ($providersToRun as $provider => $enabled) {
            if (!$enabled) {
                continue;
            }

            try {
                $this->resolveProviderConfig($provider);
            } catch (\Throwable $e) {
                AIRunResponse::updateOrCreate(
                    ['ai_run_id' => $run->id, 'provider' => $provider],
                    [
                        'model' => null,
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ]
                );
                continue;
            }

            $this->runProvider($run, $provider, $effectivePrompt);
        }

        $run->update(['status' => 'integrating']);

        if ((bool) data_get($run->options, 'run_openai_final', true)) {
            try {
                $this->resolveProviderConfig('openai');
                $this->runOpenAIFinal($run, $basePrompt, $filePromptBlock);
            } catch (\Throwable $e) {
                AIRunResponse::updateOrCreate(
                    ['ai_run_id' => $run->id, 'provider' => 'openai'],
                    [
                        'model' => null,
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ]
                );

                $run->update([
                    'status' => 'failed',
                    'meta' => array_merge($run->meta ?? [], [
                        'integration_error' => $e->getMessage(),
                    ]),
                ]);
            }
        }

        $this->refreshRunTotals($run);
    }

    protected function runProvider(AIConsensus $run, string $provider, string $prompt): AIRunResponse
    {
        $settings = $this->getProviderCredential($provider);
        $resolved = $this->resolveProviderConfig($provider, $settings);

        $responseRecord = AIRunResponse::updateOrCreate(
            ['ai_run_id' => $run->id, 'provider' => $provider],
            [
                'model' => $resolved['model'],
                'status' => 'running',
                'error' => null,
            ]
        );

        $start = microtime(true);

        try {
            $result = match ($provider) {
                'anthropic' => $this->callAnthropic($prompt, $settings),
                'gemini' => $this->callGemini($prompt, $settings),
                default => throw new \RuntimeException('Provider não suportado: ' . $provider),
            };

            $latency = (int) round((microtime(true) - $start) * 1000);
            $cost = $this->estimateProviderCost(
                $provider,
                $result['model'] ?? $responseRecord->model,
                (int) ($result['tokens_in'] ?? 0),
                (int) ($result['tokens_out'] ?? 0),
                $settings?->meta ?? []
            );

            $responseRecord->update([
                'model' => $result['model'] ?? $responseRecord->model,
                'status' => 'done',
                'raw_output' => $result['text'] ?? '',
                'error' => null,
                'tokens_in' => $result['tokens_in'] ?? null,
                'tokens_out' => $result['tokens_out'] ?? null,
                'cost_estimate_usd' => $cost,
                'latency_ms' => $latency,
                'meta' => $result['meta'] ?? [],
            ]);
        } catch (\Throwable $e) {
            $latency = (int) round((microtime(true) - $start) * 1000);

            $responseRecord->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'latency_ms' => $latency,
            ]);
        }

        return $responseRecord->fresh();
    }

    protected function runOpenAIFinal(AIConsensus $run, string $basePrompt, string $filePromptBlock = ''): void
    {
        $settings = $this->getProviderCredential('openai');
        $resolved = $this->resolveProviderConfig('openai', $settings);
        $responses = $run->responses()->where('status', 'done')->orderBy('provider')->get();

        $integrationPrompt = $this->buildIntegrationPrompt($run, $basePrompt, $responses, $filePromptBlock);
        $start = microtime(true);

        try {
            $result = $this->callOpenAI($integrationPrompt, $settings);
            $latency = (int) round((microtime(true) - $start) * 1000);
            $cost = $this->estimateProviderCost(
                'openai',
                $result['model'] ?? $resolved['model'],
                (int) ($result['tokens_in'] ?? 0),
                (int) ($result['tokens_out'] ?? 0),
                $settings?->meta ?? []
            );

            $run->update([
                'status' => 'done',
                'final_answer' => $result['text'] ?? '',
                'final_provider' => 'openai',
                'final_model' => $result['model'] ?? $resolved['model'],
                'meta' => array_merge($run->meta ?? [], [
                    'openai_latency_ms' => $latency,
                ]),
            ]);

            AIRunResponse::updateOrCreate(
                ['ai_run_id' => $run->id, 'provider' => 'openai'],
                [
                    'model' => $run->final_model,
                    'status' => 'done',
                    'raw_output' => $result['text'] ?? '',
                    'error' => null,
                    'tokens_in' => $result['tokens_in'] ?? null,
                    'tokens_out' => $result['tokens_out'] ?? null,
                    'cost_estimate_usd' => $cost,
                    'latency_ms' => $latency,
                    'meta' => $result['meta'] ?? [],
                ]
            );
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'meta' => array_merge($run->meta ?? [], [
                    'integration_error' => $e->getMessage(),
                ]),
            ]);

            AIRunResponse::updateOrCreate(
                ['ai_run_id' => $run->id, 'provider' => 'openai'],
                [
                    'model' => $resolved['model'],
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'latency_ms' => (int) round((microtime(true) - $start) * 1000),
                ]
            );
        }
    }

    protected function refreshRunTotals(AIConsensus $run): void
    {
        $run->load('responses');

        $tokensIn = (int) $run->responses->sum(fn ($item) => (int) $item->tokens_in);
        $tokensOut = (int) $run->responses->sum(fn ($item) => (int) $item->tokens_out);
        $cost = (float) $run->responses->sum(fn ($item) => (float) $item->cost_estimate_usd);

        $finalStatus = $run->final_answer
            ? 'done'
            : ($run->responses->count() > 0 && $run->responses->where('status', 'failed')->count() === $run->responses->count()
                ? 'failed'
                : $run->status);

        $run->update([
            'status' => $finalStatus,
            'total_tokens_in' => $tokensIn ?: null,
            'total_tokens_out' => $tokensOut ?: null,
            'total_cost_estimate_usd' => $cost ?: 0,
        ]);
    }

    protected function getProviderCredential(string $provider): ?AIProviderCredential
    {
        return AIProviderCredential::query()
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();
    }

    protected function resolveProviderConfig(string $provider, ?AIProviderCredential $credential = null): array
    {
        $credential = $credential ?: $this->getProviderCredential($provider);

        if (!$credential) {
            throw new \RuntimeException("Provider [$provider] sem credencial ativa na base de dados.");
        }

        if (blank($credential->api_key)) {
            throw new \RuntimeException("Provider [$provider] sem API key na base de dados.");
        }

        if (blank($credential->base_url)) {
            throw new \RuntimeException("Provider [$provider] sem base URL na base de dados.");
        }

        if (blank($credential->default_model)) {
            throw new \RuntimeException("Provider [$provider] sem modelo por defeito na base de dados.");
        }

        return [
            'credential' => $credential,
            'api_key' => (string) $credential->api_key,
            'base_url' => rtrim((string) $credential->base_url, '/'),
            'model' => (string) $credential->default_model,
            'meta' => $credential->meta ?? [],
        ];
    }

    protected function callAnthropic(string $prompt, ?AIProviderCredential $credential): array
    {
        $resolved = $this->resolveProviderConfig('anthropic', $credential);

        $response = Http::timeout((int) config('ai_consensus.http.timeout', 180))
            ->connectTimeout((int) config('ai_consensus.http.connect_timeout', 20))
            ->withHeaders([
                'x-api-key' => $resolved['api_key'],
                'anthropic-version' => '2023-06-01',
                'content-type' => 'application/json',
            ])
            ->post($resolved['base_url'] . '/v1/messages', [
                'model' => $resolved['model'],
                'max_tokens' => 4000,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Anthropic API error: ' . $response->body());
        }

        $json = $response->json();
        $text = collect($json['content'] ?? [])->pluck('text')->filter()->implode("\n\n");

        return [
            'text' => $text,
            'model' => $json['model'] ?? $resolved['model'],
            'tokens_in' => data_get($json, 'usage.input_tokens'),
            'tokens_out' => data_get($json, 'usage.output_tokens'),
            'meta' => $json,
        ];
    }

    protected function callGemini(string $prompt, ?AIProviderCredential $credential): array
    {
        $resolved = $this->resolveProviderConfig('gemini', $credential);

        $response = Http::timeout((int) config('ai_consensus.http.timeout', 180))
            ->connectTimeout((int) config('ai_consensus.http.connect_timeout', 20))
            ->post($resolved['base_url'] . '/models/' . $resolved['model'] . ':generateContent?key=' . urlencode($resolved['api_key']), [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Gemini API error: ' . $response->body());
        }

        $json = $response->json();

        return [
            'text' => data_get($json, 'candidates.0.content.parts.0.text', ''),
            'model' => $resolved['model'],
            'tokens_in' => data_get($json, 'usageMetadata.promptTokenCount'),
            'tokens_out' => data_get($json, 'usageMetadata.candidatesTokenCount'),
            'meta' => $json,
        ];
    }

    protected function callOpenAI(string $prompt, ?AIProviderCredential $credential): array
    {
        $resolved = $this->resolveProviderConfig('openai', $credential);

        $response = Http::timeout((int) config('ai_consensus.http.timeout', 180))
            ->connectTimeout((int) config('ai_consensus.http.connect_timeout', 20))
            ->withToken($resolved['api_key'])
            ->post($resolved['base_url'] . '/responses', [
                'model' => $resolved['model'],
                'input' => $prompt,
            ]);

        if (!$response->successful()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->body());
        }

        $json = $response->json();

        return [
            'text' => $this->extractOpenAIText($json),
            'model' => $json['model'] ?? $resolved['model'],
            'tokens_in' => data_get($json, 'usage.input_tokens'),
            'tokens_out' => data_get($json, 'usage.output_tokens'),
            'meta' => $json,
        ];
    }

    protected function extractOpenAIText(array $json): string
    {
        $output = data_get($json, 'output', []);

        if (is_array($output)) {
            foreach ($output as $item) {
                $content = data_get($item, 'content', []);
                if (is_array($content)) {
                    foreach ($content as $block) {
                        $text = data_get($block, 'text', null);
                        if (filled($text)) {
                            return $text;
                        }
                    }
                }
            }
        }

        return (string) (data_get($json, 'output_text', '') ?: data_get($json, 'choices.0.message.content', ''));
    }

    protected function buildIntegrationPrompt(AIConsensus $run, string $basePrompt, EloquentCollection $responses, string $filePromptBlock = ''): string
    {
        $blocks = [];
        $blocks[] = "PROMPT ORIGINAL:\n" . $basePrompt;

        if (filled($filePromptBlock)) {
            $blocks[] = "CONTEÚDO EXTRAÍDO DOS FICHEIROS:\n" . $filePromptBlock;
        }

        foreach ($responses as $response) {
            $blocks[] = strtoupper($response->provider) . " (" . ($response->model ?: 'n/a') . "):\n" . ($response->raw_output ?: '[sem conteúdo]');
        }

        $blocks[] = "INSTRUÇÕES DE INTEGRAÇÃO:\n"
            . "Produz uma resposta final consolidada, em português, clara e técnica. "
            . "Identifica convergências, divergências, riscos, lacunas e propõe uma resposta final útil e objetiva. "
            . "Se os ficheiros anexados forem relevantes, incorpora explicitamente esse conteúdo.";

        return implode("\n\n====================\n\n", $blocks);
    }

    public function buildStoredFilesPromptBlock(AIConsensus $run): string
    {
        $run->loadMissing('files');

        $chunks = [];
        foreach ($run->files as $file) {
            $text = $this->extractStoredFileText($file);
            if (blank($text)) {
                continue;
            }

            $chunks[] = "FICHEIRO: {$file->original_name}\n"
                . "MIME: " . ($file->mime_type ?: 'n/a') . "\n"
                . "CONTEÚDO EXTRAÍDO:\n"
                . $text;
        }

        return implode("\n\n------------------------------\n\n", $chunks);
    }

    protected function storeUploadedFiles(AIConsensus $run, array $files): void
    {
        $disk = config('ai_consensus.storage.disk', 'local');
        $folder = trim(config('ai_consensus.storage.folder', 'ai-consensus'), '/');

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $storedPath = $file->storeAs(
                $folder . '/' . $run->id,
                Str::uuid()->toString() . '_' . preg_replace('/[^A-Za-z0-9\.\-_]/', '_', $file->getClientOriginalName()),
                $disk
            );

            AIFile::create([
                'ai_run_id' => $run->id,
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $storedPath,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'status' => 'processed',
            ]);
        }
    }

    protected function extractStoredFileText(AIFile $file): string
    {
        $disk = config('ai_consensus.storage.disk', 'local');

        if (!$file->stored_path || !Storage::disk($disk)->exists($file->stored_path)) {
            return '';
        }

        $fullPath = Storage::disk($disk)->path($file->stored_path);
        $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));

        try {
            return match ($extension) {
                'txt', 'md', 'csv', 'json', 'xml', 'html', 'htm', 'log', 'yaml', 'yml', 'sql', 'php', 'js', 'ts', 'css' => $this->limitExtract($this->readPlainTextFile($fullPath)),
                'docx' => $this->limitExtract($this->readDocx($fullPath)),
                'pdf' => $this->limitExtract($this->readPdfBestEffort($fullPath)),
                default => '',
            };
        } catch (\Throwable $e) {
            return '';
        }
    }

    protected function readPlainTextFile(string $path): string
    {
        $content = @file_get_contents($path);

        if ($content === false) {
            return '';
        }

        return trim(mb_convert_encoding($content, 'UTF-8', 'UTF-8'));
    }

    protected function readDocx(string $path): string
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $index = $zip->locateName('word/document.xml');
        if ($index === false) {
            $zip->close();
            return '';
        }

        $xml = $zip->getFromIndex($index);
        $zip->close();

        if ($xml === false) {
            return '';
        }

        $text = strip_tags(str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xml));
        return trim(html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    protected function readPdfBestEffort(string $path): string
    {
        $content = @file_get_contents($path);
        if ($content === false) {
            return '';
        }

        preg_match_all('/\(([^()]*(?:\\\\.[^()]*)*)\)/s', $content, $matches);
        $texts = array_map(function ($item) {
            $item = preg_replace('/\\\\([nrtbf()\\\\])/', ' ', $item);
            $item = preg_replace('/\\\\\d{3}/', ' ', $item);
            return trim($item);
        }, $matches[1] ?? []);

        $joined = trim(implode(' ', array_filter($texts)));
        return preg_replace('/\s+/', ' ', $joined);
    }

    protected function limitExtract(string $text, int $maxChars = 20000): string
    {
        $text = trim($text);

        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        return mb_substr($text, 0, $maxChars) . "\n\n[conteúdo truncado]";
    }

    protected function estimateProviderCost(string $provider, string $model, int $tokensIn, int $tokensOut, array $meta = []): float
    {
        $pricing = data_get($meta, 'pricing');
        if (!is_array($pricing)) {
            $pricing = data_get(config('ai_consensus.pricing'), $provider . '.' . $model)
                ?: data_get(config('ai_consensus.pricing'), $provider . '.default');
        }

        if (!is_array($pricing)) {
            return 0.0;
        }

        $inputRate = (float) ($pricing['input_per_million'] ?? 0);
        $outputRate = (float) ($pricing['output_per_million'] ?? 0);

        $cost = (($tokensIn / 1000000) * $inputRate) + (($tokensOut / 1000000) * $outputRate);

        return round($cost, 4);
    }
}
