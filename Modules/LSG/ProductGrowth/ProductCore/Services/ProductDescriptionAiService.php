<?php

namespace Modules\LSG\ProductGrowth\ProductCore\Services;

use Illuminate\Support\Str;
use Modules\AIConsensus\Models\AIConsensusOutput;
use Modules\AIConsensus\Models\AIConsensusProvider;
use Modules\AIConsensus\Models\AIConsensusProviderResponse;
use Modules\AIConsensus\Models\AIConsensusRun;
use Modules\AIConsensus\Services\AIConsensusService;
use Modules\LSG\ProductGrowth\ProductCore\Models\Product;

class ProductDescriptionAiService
{
    private array $providerMap = [
        'anthropic' => ['key' => 'anthropic_claude', 'name' => 'Claude', 'driver' => 'anthropic'],
        'gemini' => ['key' => 'google_gemini', 'name' => 'Gemini', 'driver' => 'gemini'],
        'openai' => ['key' => 'openai_gpt', 'name' => 'OpenAI', 'driver' => 'openai'],
    ];

    public function __construct(private readonly AIConsensusService $aiConsensus)
    {
    }

    public function generate(Product $product, string $provider, string $prompt, ?int $userId = null, ?array $category = null): array
    {
        if (!isset($this->providerMap[$provider])) {
            throw new \InvalidArgumentException('Provider invalido para geracao de descricao.');
        }

        if (!$this->aiConsensus->hasActiveProviderCredential($provider)) {
            throw new \RuntimeException("Provider [{$provider}] sem credencial ativa.");
        }

        $product->loadMissing(['brand', 'supplier', 'storeProducts.store', 'productCharacteristics.characteristic']);

        $effectivePrompt = $this->buildPrompt($product, $prompt, $category);
        $providerRecord = $this->providerRecord($provider);
        $run = $this->createRun($product, $provider, $prompt, $effectivePrompt, $userId);

        try {
            $run->update(['status' => 'processing', 'started_at' => now()]);
            $result = $this->aiConsensus->executeProviderPrompt($provider, $effectivePrompt, [
                'max_output_tokens' => 1400,
                'product_growth_mode' => 'single_description_provider',
            ]);

            $description = trim((string) ($result['text'] ?? ''));

            if ($description === '') {
                throw new \RuntimeException('O provider nao devolveu uma descricao.');
            }

            AIConsensusProviderResponse::query()->create([
                'run_id' => $run->id,
                'provider_id' => $providerRecord->id,
                'status' => 'completed',
                'input_payload' => [
                    'provider' => $provider,
                    'prompt' => $effectivePrompt,
                ],
                'raw_response' => $description,
                'normalized_response' => ['description' => $description],
                'score' => 90,
                'cost_estimate' => $result['cost'] ?? 0,
                'tokens_input' => $result['tokens_in'] ?? null,
                'tokens_output' => $result['tokens_out'] ?? null,
                'latency_ms' => $result['latency_ms'] ?? null,
            ]);

            AIConsensusOutput::query()->create([
                'run_id' => $run->id,
                'output_type' => 'product_description',
                'format' => 'text',
                'content' => $description,
                'json_payload' => [
                    'description' => $description,
                    'provider' => $provider,
                ],
                'schema_valid' => true,
            ]);

            $metadata = $product->metadata ?? [];
            $metadata['ai_description_generation'] = [
                'run_id' => $run->id,
                'provider' => $provider,
                'prompt' => $prompt,
                'category' => $category,
                'description' => $description,
                'generated_at' => now()->toIso8601String(),
                'generated_by' => $userId,
                'latency_ms' => $result['latency_ms'] ?? null,
            ];

            $product->update([
                'description' => $description,
                'metadata' => $metadata,
                'updated_by' => $userId,
            ]);

            $run->update([
                'status' => 'completed',
                'final_output' => $description,
                'final_score' => 90,
                'finished_at' => now(),
            ]);

            return [
                'description' => $description,
                'run' => $run->fresh(),
                'result' => $result,
            ];
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function buildPrompt(Product $product, string $userPrompt, ?array $category): string
    {
        $context = $this->promptContext($product, $category);
        $resolvedPrompt = $this->replacePromptVariables($userPrompt, $context);

        return "TAREFA:\n"
            . "Gerar uma descricao comercial de produto/anuncio em portugues de Portugal.\n"
            . "A descricao deve ser clara, pronta para backoffice/ecommerce, sem inventar dados nao fornecidos.\n"
            . "Se faltar informacao, escreve de forma neutra e segura.\n"
            . "Nao devolvas explicacoes, analise, markdown, JSON ou alternativas. Devolve apenas a descricao final.\n\n"
            . "PEDIDO DO UTILIZADOR:\n{$resolvedPrompt}\n\n"
            . "DADOS DO ANUNCIO:\n"
            . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    private function promptContext(Product $product, ?array $category): array
    {
        $stores = $product->storeProducts
            ->map(fn ($storeProduct) => $storeProduct->store)
            ->filter()
            ->values();
        $primaryStore = $stores->first();
        $metadata = $product->metadata ?? [];
        $characteristics = $product->productCharacteristics
            ->filter(fn ($value) => $value->characteristic)
            ->mapWithKeys(fn ($value) => [
                $value->characteristic->slug => [
                    'name' => $value->characteristic->name,
                    'value' => $value->value,
                    'unit' => $value->characteristic->unit,
                    'filterable' => (bool) $value->characteristic->is_filterable,
                    'searchable' => (bool) $value->characteristic->is_searchable,
                    'seo_keyword' => (bool) $value->characteristic->is_seo_keyword,
                    'syncable' => (bool) $value->characteristic->is_syncable,
                ],
            ])
            ->all();
        $characteristicText = collect($characteristics)
            ->map(fn (array $characteristic) => trim($characteristic['name'] . ': ' . $characteristic['value'] . ($characteristic['unit'] ? ' ' . $characteristic['unit'] : '')))
            ->implode('; ');

        $context = [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->internal_sku,
                'internal_sku' => $product->internal_sku,
                'reference' => $product->reference,
                'ean' => $product->ean,
                'mpn' => $product->mpn,
                'manufacturer' => data_get($metadata, 'product_growth.manufacturer_name') ?: $product->brand?->name,
                'supplier' => data_get($metadata, 'product_growth.supplier_name') ?: $product->supplier?->name,
                'category' => data_get($metadata, 'product_growth.category_name') ?: ($category['name'] ?? null),
                'price' => $product->base_price,
                'cost' => $product->base_cost,
                'current_description' => $product->description,
                'characteristics' => $characteristics,
                'characteristic_text' => $characteristicText,
            ],
            'category' => [
                'id' => $category['id'] ?? null,
                'name' => $category['name'] ?? null,
                'slug' => $category['slug'] ?? null,
                'description' => $category['description'] ?? null,
            ],
            'store' => [
                'id' => $primaryStore?->id,
                'name' => $primaryStore?->name,
                'slug' => $primaryStore?->slug,
                'domain' => $primaryStore?->domain,
            ],
            'stores' => $stores
                ->map(fn ($store) => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'domain' => $store->domain,
                ])
                ->all(),
            'metadata' => $metadata,
        ];

        return array_merge($context, [
            'name' => $context['product']['name'],
            'sku' => $context['product']['sku'],
            'reference' => $context['product']['reference'],
            'ean' => $context['product']['ean'],
            'manufacturer' => $context['product']['manufacturer'],
            'supplier' => $context['product']['supplier'],
            'category_name' => $context['category']['name'] ?: $context['product']['category'],
            'store_name' => $context['store']['name'],
            'price' => $context['product']['price'],
            'characteristics' => $characteristicText,
        ]);
    }

    private function replacePromptVariables(string $prompt, array $context): string
    {
        return (string) preg_replace_callback('/\{\{\s*([A-Za-z0-9_.-]+)\s*\}\}/', function (array $matches) use ($context) {
            $value = data_get($context, $matches[1]);

            if ($value === null || $value === '') {
                return '';
            }

            if (is_bool($value)) {
                return $value ? 'sim' : 'nao';
            }

            if (is_array($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }

            return (string) $value;
        }, $prompt);
    }

    private function providerRecord(string $provider): AIConsensusProvider
    {
        $meta = $this->providerMap[$provider];

        return AIConsensusProvider::query()->firstOrCreate(
            ['provider_key' => $meta['key']],
            [
                'name' => $meta['name'],
                'driver' => $meta['driver'],
                'model' => config("ai_consensus.providers.{$provider}.default_model"),
                'is_active' => true,
                'priority' => 50,
                'weight' => 1,
            ]
        );
    }

    private function createRun(Product $product, string $provider, string $prompt, string $effectivePrompt, ?int $userId): AIConsensusRun
    {
        $run = AIConsensusRun::query()->create([
            'uuid' => (string) Str::uuid(),
            'source_module' => 'product_growth',
            'source_type' => 'product_description',
            'source_id' => (string) $product->id,
            'output_type' => 'product_description',
            'status' => 'pending',
            'title' => 'Product Growth description - ' . Str::limit($product->name, 80, ''),
            'input_payload' => [
                'provider' => $provider,
                'prompt' => $prompt,
                'effective_prompt' => $effectivePrompt,
            ],
            'context_payload' => [
                'product_id' => $product->id,
                'name' => $product->name,
                'sku' => $product->internal_sku,
            ],
            'options' => [
                'consensus_mode' => 'single_provider',
                'selected_provider' => $provider,
            ],
            'requested_by' => $userId,
        ]);

        $run->messages()->create([
            'role' => 'system',
            'message' => 'Product Growth single-provider description generation.',
            'payload' => ['provider' => $provider],
            'created_by' => $userId,
        ]);

        $run->messages()->create([
            'role' => 'user',
            'message' => $prompt,
            'payload' => ['product_id' => $product->id],
            'created_by' => $userId,
        ]);

        return $run;
    }
}
