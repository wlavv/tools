@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-card">
    <div class="wc-list-toolbar">
        <div>
            <span class="wc-eyebrow"><i class="fa-solid fa-store"></i> WebCatalogue</span>
            <h3>Stores</h3>
            <p class="wc-muted">Manage brand/store spaces, assets, catalogues and product ownership.</p>
        </div>
        <form class="wc-store-search" method="GET" action="{{ route('webcatalogue.stores.index') }}">
            <div class="wc-field">
                <label>Search store</label>
                <div class="wc-store-search-row">
                    <input name="q" value="{{ request('q') }}" placeholder="Name, URL, code or slug">
                    <button class="wc-primary-btn" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                    @if(request()->filled('q'))
                        <a class="wc-secondary-btn" href="{{ route('webcatalogue.stores.index') }}"><i class="fa-solid fa-xmark"></i> Clear</a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="wc-store-panel-grid">
        @forelse($items as $item)
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

                $healthChecks = [
                    ['label' => 'Domain', 'ok' => filled($item->domain)],
                    ['label' => 'WebCatalogue store', 'ok' => filled($item->slug) && ($item->status ?? null) === 'active'],
                    ['label' => 'Catalogue', 'ok' => ($item->catalogues_count ?? 0) >= 1],
                    ['label' => '5 products', 'ok' => ($item->products_count ?? 0) >= 5],
                    ['label' => '5 resources', 'ok' => ($item->resources_count ?? 0) >= 5],
                    ['label' => 'Environment', 'ok' => ($item->environments_count ?? 0) >= 1],
                    ['label' => 'Product pricing', 'ok' => ($item->products_count ?? 0) > 0 && ($item->prices_count ?? 0) >= ($item->products_count ?? 0)],
                ];
                $healthReady = collect($healthChecks)->where('ok', true)->count();
                $healthTotal = count($healthChecks);
                $healthScore = (int) round(($healthReady / max($healthTotal, 1)) * 100);
                $healthState = $healthScore >= 85 ? 'ready' : ($healthScore >= 55 ? 'almost' : 'missing');
                $missingHealth = collect($healthChecks)->where('ok', false)->pluck('label')->take(3);
            @endphp

            <article class="wc-store-panel">
                <a class="wc-store-panel-main" href="{{ route('webcatalogue.stores.show', $item) }}">
                    <div class="wc-store-summary-logo">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $item->name }} logo">
                        @else
                            <i class="fa-solid fa-store"></i>
                        @endif
                    </div>
                    <div class="wc-store-summary-head">
                        <h4>{{ $item->name ?? 'Store #'.$item->id }}</h4>
                        <span class="wc-badge wc-status-{{ $item->status ?? 'active' }}">{{ $item->status ?? 'active' }}</span>
                    </div>
                </a>

                <div class="wc-store-health">
                    <div class="wc-store-health-head">
                        <span>Store health</span>
                        <strong>{{ $healthScore }}%</strong>
                    </div>
                    <div class="wc-store-health-track"><div class="wc-store-health-fill is-{{ $healthState }}" style="width:{{ $healthScore }}%"></div></div>
                    @if($missingHealth->isNotEmpty())
                        <div class="wc-store-health-missing">
                            @foreach($missingHealth as $missing)
                                <span>{{ $missing }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="wc-store-quick-grid">
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.catalogues.index', ['id_store' => $item->id]) }}" title="Catalogues" aria-label="Catalogues"><i class="fa-solid fa-book-open"></i><strong>{{ $item->catalogues_count ?? 0 }}</strong></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.products.index', ['id_store' => $item->id]) }}" title="Products" aria-label="Products"><i class="fa-solid fa-boxes-stacked"></i><strong>{{ $item->products_count ?? 0 }}</strong></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.resources.index', ['id_store' => $item->id]) }}" title="Resources" aria-label="Resources"><i class="fa-solid fa-photo-film"></i><strong>{{ $item->resources_count ?? 0 }}</strong></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.themes.index', ['id_store' => $item->id]) }}" title="Themes" aria-label="Themes"><i class="fa-solid fa-palette"></i><strong>{{ $item->themes_count ?? 0 }}</strong></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.environments.index', ['id_store' => $item->id]) }}" title="Environments" aria-label="Environments"><i class="fa-solid fa-vr-cardboard"></i><strong>{{ $item->environments_count ?? 0 }}</strong></a>
                    <a class="wc-store-quick-link" href="{{ route('webcatalogue.pricing.index', ['id_store' => $item->id]) }}" title="Pricing" aria-label="Pricing"><i class="fa-solid fa-tags"></i><strong>{{ $item->prices_count ?? 0 }}</strong></a>
                </div>
            </article>
        @empty
            <div class="wc-list-empty"><i class="fa-solid fa-store"></i><div><strong>No stores found.</strong><br><span>Create the first store or adjust the search filter.</span></div></div>
        @endforelse
    </div>

    @if($items instanceof \Illuminate\Contracts\Pagination\Paginator || $items instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="wc-pagination">{{ $items->links() }}</div>
    @endif
</div>
</div>
@endsection
