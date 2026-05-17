@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif
@php
    $logoResource = $item->logoResource;
    $logoResourcePath = trim((string) ($logoResource?->file_path ?? ''));
    $logoResourceUrl = trim((string) ($logoResource?->public_url ?? ''));
    $logoPath = trim((string) ($item->logo_path ?? ''));
    $logoUrl = null;

    if ($logoResourcePath !== '' && file_exists(storage_path('app/public/' . ltrim($logoResourcePath, '/')))) {
        $logoUrl = '/storage/' . ltrim($logoResourcePath, '/');
    }

    if (!$logoUrl && $logoPath !== '') {
        $normalizedLogoPath = ltrim($logoPath, '/');
        $logoUrl = match (true) {
            str_starts_with($logoPath, 'http://'), str_starts_with($logoPath, 'https://') => $logoPath,
            str_starts_with($normalizedLogoPath, 'storage/') => '/' . $normalizedLogoPath,
            default => '/storage/' . $normalizedLogoPath,
        };
    }

    if (!$logoUrl && $logoResourceUrl !== '') {
        $normalizedResourceUrl = ltrim($logoResourceUrl, '/');
        $logoUrl = match (true) {
            str_starts_with($logoResourceUrl, 'http://'), str_starts_with($logoResourceUrl, 'https://') => $logoResourceUrl,
            str_starts_with($normalizedResourceUrl, 'storage/') => '/' . $normalizedResourceUrl,
            str_contains($normalizedResourceUrl, '/storage/') => '/' . preg_replace('#^.*?/storage/#', 'storage/', $normalizedResourceUrl),
            default => 'http://' . $normalizedResourceUrl,
        };
    }
    $domain = $item->domain ? preg_replace('#^https?://#', '', $item->domain) : null;
    $domainUrl = $domain ? 'https://' . $domain : null;
    $frontUrl = !empty($item->slug) ? route('webcatalogue.front.store.show', $item->slug) : null;
    $environment = ($item->environments ?? collect())->first();
    $metadata = is_array($item->metadata ?? null) ? $item->metadata : [];
    $contact = is_array($metadata['contact'] ?? null) ? $metadata['contact'] : $metadata;
    $storeDetails = [
        'Email' => $item->email ?? $contact['email'] ?? $contact['contact_email'] ?? null,
        'Phone' => $item->phone ?? $contact['phone'] ?? $contact['telephone'] ?? $contact['mobile'] ?? null,
        'Address' => $item->address ?? $contact['address'] ?? $contact['street'] ?? null,
        'City' => $item->city ?? $contact['city'] ?? null,
        'Postal code' => $item->postal_code ?? $contact['postal_code'] ?? $contact['zip'] ?? null,
        'Country' => $item->country ?? $contact['country'] ?? null,
        'VAT / Fiscal ID' => $item->vat_number ?? $contact['vat_number'] ?? $contact['fiscal_id'] ?? $contact['tax_id'] ?? null,
    ];
@endphp

<div class="wc-editor-layout">
    <div>
        <div class="wc-detail-hero wc-detail-hero-store">
            <div>
                <span class="wc-eyebrow"><i class="fa-solid fa-store"></i> Store</span>
                <h2>{{ $item->name ?? 'Store' }}</h2>
                <p>{{ $item->description ?? $item->short_description ?? 'Structured WebCatalogue record.' }}</p>
                <div class="wc-detail-tags">
                    <span class="wc-badge">{{ $item->status ?? 'active' }}</span>
                    @if(!empty($item->code))<span class="wc-badge">{{ $item->code }}</span>@endif
                </div>
            </div>
            <div class="wc-detail-icon"><i class="fa-solid fa-store"></i></div>
        </div>

        <details class="wc-collapsible-card wc-spaced-card">
            <summary>
                <div class="wc-section-head">
                    <div><span class="wc-eyebrow"><i class="fa-solid fa-book-open"></i> Catalogues</span><h3>Latest catalogues</h3></div>
                </div>
            </summary>
            <div class="wc-collapsible-body">
                <div class="wc-inline-list">
                    @forelse($item->catalogues ?? [] as $catalogue)
                        <a class="wc-inline-row" href="{{ route('webcatalogue.catalogues.show', $catalogue) }}">
                            <div><strong>{{ $catalogue->name }}</strong><span>{{ $catalogue->catalogue_type ?? 'showcase' }} - {{ $catalogue->products_count ?? 0 }} products</span></div>
                            <span class="wc-badge wc-status-{{ $catalogue->status ?? 'draft' }}">{{ $catalogue->status ?? 'draft' }}</span>
                        </a>
                    @empty
                        <div class="wc-empty-state"><i class="fa-solid fa-book-open"></i><span>No catalogues for this store yet.</span></div>
                    @endforelse
                </div>
            </div>
        </details>

        <details class="wc-collapsible-card wc-spaced-card">
            <summary>
                <div class="wc-section-head">
                    <div><span class="wc-eyebrow"><i class="fa-solid fa-boxes-stacked"></i> Products</span><h3>Latest products</h3></div>
                </div>
            </summary>
            <div class="wc-collapsible-body">
                <div class="wc-inline-list">
                    @forelse($item->products ?? [] as $product)
                        <a class="wc-inline-row" href="{{ route('webcatalogue.products.show', $product) }}">
                            <div><strong><span class="wc-html-inline">{!! $product->name ?: $product->reference !!}</span></strong><span>{{ $product->reference }} - readiness {{ $product->readinessScore() }}%</span></div>
                            <span class="wc-badge wc-status-{{ $product->status ?? 'draft' }}">{{ $product->status ?? 'draft' }}</span>
                        </a>
                    @empty
                        <div class="wc-empty-state"><i class="fa-solid fa-box"></i><span>No products for this store yet.</span></div>
                    @endforelse
                </div>
            </div>
        </details>

        <details class="wc-collapsible-card wc-spaced-card">
            <summary>
                <div class="wc-section-head">
                    <div><span class="wc-eyebrow"><i class="fa-solid fa-photo-film"></i> Resources</span><h3>Latest resources</h3></div>
                </div>
            </summary>
            <div class="wc-collapsible-body">
                <div class="wc-inline-list">
                    @forelse($item->resources ?? [] as $resource)
                        <a class="wc-inline-row" href="{{ route('webcatalogue.resources.show', $resource) }}">
                            <div><strong>{{ $resource->title ?: $resource->filename ?: 'Resource #'.$resource->id }}</strong><span>{{ $resource->resource_type }} @if($resource->product)- {{ $resource->product->reference }}@endif @if($resource->catalogue)- {{ $resource->catalogue->name }}@endif</span></div>
                            <span class="wc-badge wc-status-{{ $resource->status ?? 'active' }}">{{ $resource->status ?? 'active' }}</span>
                        </a>
                    @empty
                        <div class="wc-empty-state"><i class="fa-solid fa-photo-film"></i><span>No resources for this store yet.</span></div>
                    @endforelse
                </div>
            </div>
        </details>

        <details class="wc-collapsible-card wc-spaced-card">
            <summary>
                <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-list-check"></i> Details</span><h3>Record information</h3></div></div>
            </summary>
            <div class="wc-collapsible-body">
                <div class="wc-keyval-grid">
                    @foreach($item->getAttributes() as $key => $value)
                        @continue(in_array($key, ['metadata','description','short_description','logo_path','created_at','updated_at']))
                        <div class="wc-keyval"><span>{{ str_replace('_', ' ', $key) }}</span><strong>{{ is_scalar($value) || is_null($value) ? ($value ?? '-') : json_encode($value) }}</strong></div>
                    @endforeach
                </div>
            </div>
        </details>

        @if(!empty($item->metadata))
        <details class="wc-collapsible-card wc-spaced-card">
            <summary>
                <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-code"></i> Metadata</span><h3>Advanced metadata</h3></div></div>
            </summary>
            <div class="wc-collapsible-body">
                <pre class="wc-json-preview">{{ json_encode($item->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </details>
        @endif
    </div>

    <aside class="wc-preview-panel">
        <div class="wc-preview-card">
            <div class="wc-preview-body">
                <div class="wc-store-summary-logo">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $item->name }} logo">
                    @else
                        <i class="fa-solid fa-store"></i>
                    @endif
                </div>
                <div class="wc-store-summary-head">
                    <h4>{{ $item->name }}</h4>
                    <span class="wc-badge wc-status-{{ $item->status ?? 'active' }}">{{ $item->status ?? 'active' }}</span>
                </div>

                <div class="wc-store-detail-list">
                    <div class="wc-store-detail-item"><span>Code</span><strong>{{ $item->code ?? '-' }}</strong></div>
                    <div class="wc-store-detail-item"><span>Official domain</span>@if($domainUrl)<a href="{{ $domainUrl }}" target="_blank" rel="noopener">{{ $domain }}</a>@else<strong>-</strong>@endif</div>
                    <div class="wc-store-detail-item"><span>Public catalogue</span>@if($frontUrl)<a href="{{ $frontUrl }}" target="_blank" rel="noopener">Open front</a>@else<strong>-</strong>@endif</div>
                </div>

                <div class="wc-store-quick-grid">
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.catalogues.index', ['id_store' => $item->id]) }}"><i class="fa-solid fa-book-open"></i><strong>{{ $item->catalogues_count ?? 0 }}</strong><span>Catalogues</span></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.products.index', ['id_store' => $item->id]) }}"><i class="fa-solid fa-boxes-stacked"></i><strong>{{ $item->products_count ?? 0 }}</strong><span>Products</span></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.resources.index', ['id_store' => $item->id]) }}"><i class="fa-solid fa-photo-film"></i><strong>{{ $item->resources_count ?? 0 }}</strong><span>Resources</span></a>
                </div>

                @if($environment)
                    <a class="wc-store-environment-link" href="{{ route('webcatalogue.environments.show', $environment) }}"><span><i class="fa-solid fa-vr-cardboard"></i> Environment: {{ $environment->name }}</span><i class="fa-solid fa-arrow-right"></i></a>
                @else
                    <div class="wc-store-environment-link"><span><i class="fa-solid fa-vr-cardboard"></i> No environment defined</span></div>
                @endif
            </div>
        </div>

        <div class="wc-preview-card">
            <div class="wc-preview-body">
                <h4>Store details</h4>
                <div class="wc-store-detail-list">
                    @foreach($storeDetails as $label => $value)
                        @continue(blank($value))
                        <div class="wc-store-detail-item">
                            <span>{{ $label }}</span>
                            @if($label === 'Email')
                                <a href="mailto:{{ $value }}">{{ $value }}</a>
                            @elseif($label === 'Phone')
                                <a href="tel:{{ preg_replace('/\s+/', '', $value) }}">{{ $value }}</a>
                            @else
                                <strong>{{ $value }}</strong>
                            @endif
                        </div>
                    @endforeach
                    @if(collect($storeDetails)->filter()->isEmpty())
                        <div class="wc-empty-state"><i class="fa-solid fa-circle-info"></i><span>No extra contact details defined yet.</span></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="wc-preview-card">
            <div class="wc-preview-body">
                <h4>Store zones</h4>
                <div class="wc-store-quick-grid">
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.themes.index', ['id_store' => $item->id]) }}"><i class="fa-solid fa-palette"></i><strong>{{ $item->themes_count ?? 0 }}</strong><span>Themes</span></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.environments.index', ['id_store' => $item->id]) }}"><i class="fa-solid fa-vr-cardboard"></i><strong>{{ $item->environments_count ?? 0 }}</strong><span>Environments</span></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.pricing.index', ['id_store' => $item->id]) }}"><i class="fa-solid fa-tags"></i><strong>{{ $item->prices_count ?? 0 }}</strong><span>Pricing</span></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.promotions.index', ['id_store' => $item->id]) }}"><i class="fa-solid fa-bullhorn"></i><strong>{{ $item->promotions_count ?? 0 }}</strong><span>Promotions</span></a>
                </div>
            </div>
        </div>

        <div class="wc-preview-card">
            <div class="wc-preview-body">
                <h4>Visual Recognition</h4>
                <p class="wc-muted">Rebuild search fingerprints or OpenCV visual markers for this store only.</p>
                @if($item->latestFingerprintRebuildLog)
                    <div class="wc-store-detail-list">
                        <div class="wc-store-detail-item"><span>Last rebuild</span><strong>{{ $item->latestFingerprintRebuildLog->finished_at?->format('Y-m-d H:i') ?: $item->latestFingerprintRebuildLog->created_at?->format('Y-m-d H:i') }}</strong></div>
                        <div class="wc-store-detail-item"><span>Status</span><strong>{{ $item->latestFingerprintRebuildLog->status }}</strong></div>
                        <div class="wc-store-detail-item"><span>Images</span><strong>{{ $item->latestFingerprintRebuildLog->processed }} / {{ $item->latestFingerprintRebuildLog->failed_count }} failed</strong></div>
                    </div>
                @else
                    <div class="wc-empty-state"><i class="fa-solid fa-fingerprint"></i><span>No fingerprint rebuild logged yet.</span></div>
                @endif
                <form method="post" action="{{ route('webcatalogue.stores.recognition.fingerprints.rebuild', $item) }}">
                    @csrf
                    <button class="wc-secondary-btn wc-full-action" type="submit"><i class="fa-solid fa-rotate"></i> Rebuild fingerprints</button>
                </form>
                <form method="post" action="{{ route('webcatalogue.stores.recognition.markers.rebuild', $item) }}">
                    @csrf
                    <button class="wc-secondary-btn wc-full-action" type="submit"><i class="fa-solid fa-location-crosshairs"></i> Generate visual markers</button>
                </form>
            </div>
        </div>

        <div class="wc-preview-card">
            <div class="wc-preview-body">
                <h4>Publish flow</h4>
                <p class="wc-muted">Preview protected by token and public link with basic tracking.</p>
                <a class="wc-primary-btn wc-full-action" href="{{ route('webcatalogue.stores.publish.show', $item) }}"><i class="fa-solid fa-paper-plane"></i> Open publish flow</a>
            </div>
        </div>
    </aside>
</div>
</div>
@endsection
