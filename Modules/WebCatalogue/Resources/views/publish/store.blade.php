@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
    @if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

    @php
        $previewLink = $stats['preview_link'] ?? null;
        $publicLink = $stats['public_link'] ?? null;
        $returnTo = route('webcatalogue.stores.publish.show', $store);
        $steps = [
            ['label' => 'Store', 'icon' => 'fa-store', 'ok' => $stats['has_store'], 'hint' => 'Nome, slug e dominio definidos', 'url' => route('webcatalogue.stores.edit', ['store' => $store, 'return_to' => $returnTo])],
            ['label' => 'Catalogue', 'icon' => 'fa-book-open', 'ok' => $stats['publishable_catalogues'] > 0, 'hint' => $stats['publishable_catalogues'].' com produtos', 'url' => route('webcatalogue.catalogues.index', ['id_store' => $store->id, 'return_to' => $returnTo])],
            ['label' => 'Products', 'icon' => 'fa-boxes-stacked', 'ok' => $stats['ready_products']->count() > 0, 'hint' => $stats['ready_products']->count().' de '.$stats['products'].' prontos', 'url' => route('webcatalogue.products.index', ['id_store' => $store->id, 'return_to' => $returnTo])],
            ['label' => 'Theme', 'icon' => 'fa-palette', 'ok' => $stats['has_theme'], 'hint' => $stats['themes'].' themes', 'url' => $stats['themes'] > 0 ? route('webcatalogue.themes.index', ['id_store' => $store->id, 'return_to' => $returnTo]) : route('webcatalogue.themes.create', ['id_store' => $store->id, 'return_to' => $returnTo])],
            ['label' => 'Preview', 'icon' => 'fa-eye', 'ok' => (bool) $previewLink, 'hint' => $previewLink ? 'Preview protegido ativo' : 'Gerar preview', 'url' => $previewLink ? route('webcatalogue.front.preview.store', $previewLink->token) : null],
            ['label' => 'Publish', 'icon' => 'fa-paper-plane', 'ok' => (bool) $publicLink, 'hint' => $publicLink ? 'Link publico ativo' : 'Por publicar', 'url' => $publicLink ? route('webcatalogue.front.public_link', $publicLink->token) : null],
        ];
    @endphp

    <div class="wc-detail-hero wc-detail-hero-store">
        <div>
            <span class="wc-eyebrow"><i class="fa-solid fa-paper-plane"></i> Publish flow</span>
            <h2>{{ $store->name }}</h2>
            <p>Store -> Catalogue -> Products -> Theme -> Preview -> Publish</p>
            <div class="wc-detail-tags">
                <span class="wc-badge wc-status-{{ $store->status ?? 'active' }}">{{ $store->status ?? 'active' }}</span>
                @if($publicLink)<span class="wc-badge wc-status-active">{{ $publicLink->tracked_views }} views</span>@endif
            </div>
        </div>
        <div class="wc-detail-icon"><i class="fa-solid fa-paper-plane"></i></div>
    </div>

    <div class="wc-publish-grid">
        @foreach($steps as $step)
            @php $stepTag = !empty($step['url']) ? 'a' : 'div'; @endphp
            <{{ $stepTag }} class="wc-publish-step {{ $step['ok'] ? 'is-done' : 'is-missing' }} {{ !empty($step['url']) ? 'is-clickable' : '' }}" @if(!empty($step['url'])) href="{{ $step['url'] }}" @if(in_array($step['label'], ['Preview','Publish'], true)) target="_blank" rel="noopener" @endif @endif>
                <i class="fa-solid {{ $step['icon'] }}"></i>
                <strong>{{ $step['label'] }}</strong>
                <span>{{ $step['hint'] }}</span>
                @if(!empty($step['url']))
                    <em>{{ $step['ok'] ? 'Open' : 'Fix' }} <i class="fa-solid fa-arrow-right"></i></em>
                @endif
            </{{ $stepTag }}>
        @endforeach
    </div>

    <div class="wc-editor-layout">
        <div>
            <div class="wc-card wc-spaced-card">
                <div class="wc-section-head">
                    <div>
                        <span class="wc-eyebrow"><i class="fa-solid fa-list-check"></i> Readiness</span>
                        <h3>Publication checklist</h3>
                    </div>
                </div>
                <div class="wc-publish-metrics">
                    <div class="wc-publish-metric" title="Catalogues"><i class="fa-solid fa-book-open"></i><span>Catalogues</span><strong>{{ $stats['catalogues'] }}</strong></div>
                    <div class="wc-publish-metric" title="Publishable catalogues"><i class="fa-solid fa-circle-check"></i><span>Publishable</span><strong>{{ $stats['publishable_catalogues'] }}</strong></div>
                    <div class="wc-publish-metric" title="Products"><i class="fa-solid fa-boxes-stacked"></i><span>Products</span><strong>{{ $stats['products'] }}</strong></div>
                    <div class="wc-publish-metric" title="Products ready"><i class="fa-solid fa-rocket"></i><span>Products ready</span><strong>{{ $stats['ready_products']->count() }}</strong></div>
                    <div class="wc-publish-metric" title="Themes"><i class="fa-solid fa-palette"></i><span>Themes</span><strong>{{ $stats['themes'] }}</strong></div>
                    <div class="wc-publish-metric" title="Environments"><i class="fa-solid fa-vr-cardboard"></i><span>Env.</span><strong>{{ $stats['environments'] }}</strong></div>
                    <div class="wc-publish-metric" title="Pricing rows"><i class="fa-solid fa-tags"></i><span>Pricing</span><strong>{{ $stats['prices'] }}</strong></div>
                </div>
            </div>

            <div class="wc-card wc-spaced-card">
                <div class="wc-section-head">
                    <div>
                        <span class="wc-eyebrow"><i class="fa-solid fa-link"></i> Public links</span>
                        <h3>State and tracking</h3>
                    </div>
                </div>
                <table class="wc-table">
                    <thead><tr><th>Type</th><th>Status</th><th>Views</th><th>URL</th></tr></thead>
                    <tbody>
                        @forelse($store->publicLinks as $link)
                            <tr>
                                <td>{{ $link->link_type }}</td>
                                <td><span class="wc-badge wc-status-{{ $link->status }}">{{ $link->status }}</span></td>
                                <td>{{ $link->tracked_views }}</td>
                                <td>
                                    @if($link->status === 'preview')
                                        <a href="{{ route('webcatalogue.front.preview.store', $link->token) }}" target="_blank" rel="noopener">Open preview</a>
                                    @elseif($link->status === 'active')
                                        <a href="{{ route('webcatalogue.front.public_link', $link->token) }}" target="_blank" rel="noopener">Open public</a>
                                    @else
                                        <span class="wc-muted">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No public links created yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <aside class="wc-preview-panel">
            <div class="wc-preview-card">
                <div class="wc-preview-body">
                    <h4>Preview</h4>
                    <p class="wc-muted">Preview publico por token antes da publicacao.</p>
                    <form method="post" action="{{ route('webcatalogue.stores.publish.preview', $store) }}">
                        @csrf
                        <button class="wc-secondary-btn" type="submit"><i class="fa-solid fa-eye"></i> Generate preview</button>
                    </form>
                    @if($previewLink)
                        <a class="wc-action-link wc-full-action" href="{{ route('webcatalogue.front.preview.store', $previewLink->token) }}" target="_blank" rel="noopener"><i class="fa-solid fa-up-right-from-square"></i> Open preview</a>
                    @endif
                </div>
            </div>

            <div class="wc-preview-card">
                <div class="wc-preview-body">
                    <h4>Publish</h4>
                    <p class="wc-muted">Publica catalogues com produtos e produtos com readiness completo.</p>
                    <form method="post" action="{{ route('webcatalogue.stores.publish.publish', $store) }}">
                        @csrf
                        <button class="wc-primary-btn" type="submit"><i class="fa-solid fa-paper-plane"></i> Publish store</button>
                    </form>
                    @if($publicLink)
                        <a class="wc-action-link wc-full-action" href="{{ route('webcatalogue.front.public_link', $publicLink->token) }}" target="_blank" rel="noopener"><i class="fa-solid fa-link"></i> Open public link</a>
                        <form method="post" action="{{ route('webcatalogue.stores.publish.unpublish', $store) }}">
                            @csrf
                            <button class="wc-secondary-btn wc-full-action" type="submit"><i class="fa-solid fa-ban"></i> Disable public link</button>
                        </form>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
