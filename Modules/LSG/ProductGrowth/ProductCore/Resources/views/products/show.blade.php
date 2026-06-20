@extends('product-core::layouts.module')

@section('module-content')
@php
    $status = $product->status;
    $aiStore = $aiDescription['store'] ?? null;
    $aiCategories = collect($aiDescription['categories'] ?? []);
    $aiDefaultPrompt = $aiDescription['default_prompt'] ?? '';
    $aiSelectedCategoryRef = $aiDescription['selected_category_ref'] ?? '';
    $workflowContent = $product->metadata['workflow_steps'] ?? [];
    $lastAiDescription = $product->metadata['ai_description_generation'] ?? null;
    $primaryAsset = $product->primary_asset;
    $previewAsset = $product->assets->firstWhere('asset_role', 'cover_image')
        ?: $product->assets->firstWhere('asset_role', 'main_image')
        ?: $primaryAsset;
    $previewAssetUrl = $previewAsset?->public_url;
    $previewAssetPath = $previewAssetUrl ? parse_url($previewAssetUrl, PHP_URL_PATH) : null;
    if ($previewAssetPath && \Illuminate\Support\Str::contains($previewAssetPath, '/storage/')) {
        $previewAssetUrl = '/storage/' . \Illuminate\Support\Str::after($previewAssetPath, '/storage/');
    } elseif (is_string($previewAssetUrl) && \Illuminate\Support\Str::startsWith($previewAssetUrl, 'storage/')) {
        $previewAssetUrl = '/' . $previewAssetUrl;
    }
    $previewStoreProduct = $product->storeProducts->first();
@endphp

@include('product-core::partials.product-timeline', [
    'productGrowthTimelineShowPreviewButton' => true,
])

<div class="pc-preview-modal" data-product-preview-modal aria-hidden="true" hidden>
    <div class="pc-preview-modal__backdrop" data-product-preview-close></div>
    <div class="pc-preview-modal__dialog" role="dialog" aria-modal="true" aria-label="Preview BO">
        <div class="pc-preview-modal__head">
            <div>
                <strong>Preview BO</strong>
                <small>{{ $previewStoreProduct?->store?->name ?? 'Core' }}</small>
            </div>
            <button type="button" class="pc-icon-action" data-product-preview-close aria-label="Fechar preview">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    <div class="pc-product-preview">
        <div class="pc-product-preview__media">
            @if($previewAssetUrl)
                <div
                    class="pc-product-preview__image"
                    role="img"
                    aria-label="{{ $previewAsset?->title ?: $product->name }}"
                    style="background-image:url('{{ $previewAssetUrl }}')"
                ></div>
            @else
                <div class="pc-product-preview__placeholder">
                    <i class="fa-solid fa-box-open"></i>
                </div>
            @endif
        </div>

        <div class="pc-product-preview__content">
            <div class="pc-product-preview__eyebrow">{{ $product->brand?->name ?? 'LSG Product Growth' }}</div>
            <h3>{{ $previewStoreProduct?->name ?: $product->name }}</h3>
            <p>{{ $previewStoreProduct?->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 180) }}</p>

            <div class="pc-product-preview__meta">
                <span><i class="fa-solid fa-barcode"></i> {{ $product->internal_sku }}</span>
                <span><i class="fa-solid fa-tag"></i> {{ $product->base_price ? number_format($product->base_price, 2, ',', '.') . ' EUR' : '-' }}</span>
                <span><i class="fa-solid fa-gauge-high"></i> {{ number_format($product->data_quality_score, 0) }}%</span>
            </div>

            <div class="pc-product-preview__badges">
                <span class="pc-badge">{{ config('product-core.states.' . $product->status, $product->status) }}</span>
                <span class="pc-badge">{{ $product->assets->count() }} assets</span>
                <span class="pc-badge">{{ $product->storeProducts->count() }} lojas</span>
            </div>
        </div>
    </div>
    </div>
</div>

<section class="product-core-card pc-panel">
    <div class="pc-panel-head">
        <div>
            <h2 class="pc-panel-title">Gerar descricao com IA</h2>
            <p class="pc-panel-subtitle">Escreve o prompt e escolhe apenas uma das IAs do AI Consensus para gerar a descricao do anuncio.</p>
        </div>
        @if(!empty($lastAiDescription['run_id']))
            <span class="pc-badge">Run #{{ $lastAiDescription['run_id'] }}</span>
        @endif
    </div>

    <form method="POST" action="{{ route('product_growth.product_core.products.generate_description', $product) }}" class="pc-ai-description-form">
        @csrf
        <input type="hidden" name="ai_category_ref" data-ai-category-id value="{{ old('ai_category_ref', $aiSelectedCategoryRef) }}">

        <div class="pc-ai-provider-row">
            @foreach([
                'anthropic' => ['label' => 'Claude', 'icon' => 'fa-solid fa-brain'],
                'gemini' => ['label' => 'Gemini', 'icon' => 'fa-solid fa-gem'],
                'openai' => ['label' => 'OpenAI', 'icon' => 'fa-solid fa-circle-nodes'],
            ] as $providerKey => $providerMeta)
                <label class="pc-ai-provider-option">
                    <input type="radio" name="provider" value="{{ $providerKey }}" @checked(old('provider', $lastAiDescription['provider'] ?? 'openai') === $providerKey)>
                    <span><i class="{{ $providerMeta['icon'] }}"></i></span>
                    <strong>{{ $providerMeta['label'] }}</strong>
                </label>
            @endforeach
        </div>

        <div class="pc-ai-prompt-context"
             data-ai-category-builder
             data-categories='@json($aiCategories, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'
             data-default-prompt='@json($aiDefaultPrompt, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG)'>
            <div class="pc-ai-context-store">
                <span><i class="fa-solid fa-store"></i></span>
                <div>
                    <small>Loja</small>
                    <strong>{{ $aiStore?->name ?? 'Sem loja associada' }}</strong>
                </div>
            </div>

            <div class="pc-ai-category-levels" data-ai-category-levels>
                @if($aiCategories->isEmpty())
                    <span class="text-muted">Sem categorias configuradas para esta loja.</span>
                @endif
            </div>
        </div>

        <label class="pc-label mt-3">Prompt</label>
        <textarea class="pc-textarea" name="prompt" rows="5" required placeholder="Ex: cria uma descricao premium para ecommerce, com tom colecionavel, destacando estado, edicao, raridade e valor para colecionadores.">{{ old('prompt', $lastAiDescription['prompt'] ?? $aiDefaultPrompt) }}</textarea>

        @if(!empty($lastAiDescription['description']))
            <div class="pc-ai-last-result">
                <small>Ultima descricao gerada</small>
                <p>{{ \Illuminate\Support\Str::limit($lastAiDescription['description'], 260) }}</p>
            </div>
        @endif

        <div class="pc-form-actions">
            <button type="submit" class="lsg-action-btn lsg-action-btn--primary">
                <span class="lsg-action-btn__icon"><i class="fa-solid fa-wand-magic-sparkles"></i></span>
                <span class="lsg-action-btn__label">Gerar descricao</span>
            </button>
        </div>
    </form>
</section>

@if(!empty($workflowContent))
<section class="product-core-card pc-panel">
    <div class="pc-panel-head">
        <div>
            <h2 class="pc-panel-title">Conteudo por etapa</h2>
            <p class="pc-panel-subtitle">Resumo operacional preenchido em cada passo do workflow.</p>
        </div>
    </div>

    <div class="pc-workflow-content-grid">
        @foreach($workflowContent as $step)
            <div class="pc-workflow-content-card">
                <div class="d-flex justify-content-between gap-2 align-items-start">
                    <strong>{{ $step['title'] ?? '-' }}</strong>
                    <span class="pc-badge">{{ $step['status'] ?? '-' }}</span>
                </div>
                <small>{{ $step['owner'] ?? '-' }}</small>
                <p>{{ $step['content'] ?? '-' }}</p>
                <em>{{ $step['output'] ?? '-' }}</em>
            </div>
        @endforeach
    </div>
</section>
@endif

<section class="pc-product-summary">
    <div class="product-core-card pc-panel">
        <div class="pc-panel-head">
            <div>
                <h2 class="pc-panel-title">Dados principais</h2>
                <p class="pc-panel-subtitle">Fonte central para todos os canais e equipas.</p>
            </div>
        </div>

        <dl class="pc-kv">
            <dt>SKU</dt><dd><code>{{ $product->internal_sku }}</code></dd>
            <dt>Referencia/EAN</dt><dd>{{ $product->reference ?: '-' }} / {{ $product->ean ?: '-' }}</dd>
            <dt>Marca</dt><dd>{{ $product->brand?->name ?? '-' }}</dd>
            <dt>Fornecedor</dt><dd>{{ $product->supplier?->name ?? '-' }}</dd>
            <dt>Estado</dt><dd><span class="pc-badge">{{ $product->status }}</span></dd>
            <dt>Qualidade</dt><dd>{{ number_format($product->data_quality_score,0) }}%</dd>
            <dt>Descricao</dt><dd>{!! nl2br(e($product->description)) ?: '-' !!}</dd>
        </dl>
    </div>

    <div class="product-core-card pc-panel">
        <div class="pc-panel-head">
            <div>
                <h2 class="pc-panel-title">Comercial</h2>
                <p class="pc-panel-subtitle">Base de custo, preco e cobertura.</p>
            </div>
        </div>

        <dl class="pc-kv">
            <dt>Custo base</dt><dd>{{ $product->base_cost ? number_format($product->base_cost,2,',','.') . ' EUR' : '-' }}</dd>
            <dt>Preco base</dt><dd>{{ $product->base_price ? number_format($product->base_price,2,',','.') . ' EUR' : '-' }}</dd>
            <dt>Assets</dt><dd>{{ $product->assets->count() }}</dd>
            <dt>Lojas</dt><dd>{{ $product->storeProducts->count() }}</dd>
        </dl>
    </div>
</section>

<section class="product-core-card pc-panel">
    <div class="pc-panel-head">
        <div>
            <h2 class="pc-panel-title">Produtos por loja</h2>
            <p class="pc-panel-subtitle">Canais onde o produto existe e estado de sincronizacao.</p>
        </div>
    </div>

    <div class="product-core-table-wrap">
        <table class="product-core-table">
            <thead><tr><th>Loja</th><th>Nome</th><th>Preco</th><th>Ativo venda</th><th>Sync PS</th><th>Estado sync</th></tr></thead>
            <tbody>
            @forelse($product->storeProducts as $sp)
                <tr>
                    <td>{{ $sp->store?->name }}</td>
                    <td>{{ $sp->name ?: $product->name }}</td>
                    <td>{{ $sp->sale_price ? number_format($sp->sale_price,2,',','.') . ' EUR' : '-' }}</td>
                    <td>{{ $sp->active_for_sale ? 'Sim':'Nao' }}</td>
                    <td>{{ $sp->sync_to_prestashop ? 'Sim':'Nao' }}</td>
                    <td><span class="pc-badge">{{ $sp->sync_status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Sem lojas associadas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
