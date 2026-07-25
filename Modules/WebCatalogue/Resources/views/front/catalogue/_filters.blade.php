@php
    $selectedResources = (array) request()->query('resources', []);
    $isTcgStore = isset($store) && ($store->slug ?? null) === 'tcg-collectors';
    $tcgResourceLabels = [
        'image' => 'Com imagem',
        'price' => 'Com preco',
        '3d' => '3D',
        'ar' => 'AR',
        'vr' => 'VR',
        'video' => 'Video',
        'audio' => 'Audio',
        'document' => 'Docs',
    ];
@endphp
<form class="wc-filter-panel" method="GET">
    <div class="wc-filter-main">
        <div class="wc-filter-field wc-filter-search">
            <label>{{ $isTcgStore ? 'Encontrar carta' : 'Search' }}</label>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ $isTcgStore ? 'Nome, numero, set, artista...' : 'Name, reference, SKU, brand...' }}">
        </div>
        <div class="wc-filter-field">
            <label>{{ $isTcgStore ? 'Jogo' : 'Brand' }}</label>
            <select name="brand">
                <option value="">{{ $isTcgStore ? 'Todos os jogos' : 'All brands' }}</option>
                @foreach(($filters['brands'] ?? []) as $brand)
                    <option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>
                @endforeach
            </select>
        </div>
        <div class="wc-filter-field">
            <label>{{ $isTcgStore ? 'Tipo de carta' : 'Category' }}</label>
            <select name="category">
                <option value="">{{ $isTcgStore ? 'Todos os tipos' : 'All categories' }}</option>
                @foreach(($filters['categories'] ?? []) as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="wc-resource-filters" aria-label="Resource filters">
        @foreach(($filters['resource_options'] ?? []) as $key => $option)
            <label class="wc-resource-filter @if(in_array($key, $selectedResources, true)) is-active @endif">
                <input type="checkbox" name="resources[]" value="{{ $key }}" @checked(in_array($key, $selectedResources, true))>
                <i class="{{ $option['icon'] }}"></i>
                <span>{{ $isTcgStore ? ($tcgResourceLabels[$key] ?? $option['label']) : $option['label'] }}</span>
                @if(isset($option['count']))<small>{{ $option['count'] }}</small>@endif
            </label>
        @endforeach
    </div>

    <div class="wc-filter-actions">
        <button class="wc-btn wc-btn-primary" type="submit"><i class="fa-solid fa-filter"></i> {{ $isTcgStore ? 'Filtrar cartas' : 'Apply filters' }}</button>
        <a class="wc-btn" href="{{ url()->current() }}"><i class="fa-solid fa-rotate-left"></i> {{ $isTcgStore ? 'Limpar' : 'Clear' }}</a>
    </div>
</form>

@once
    @push('styles')
        <style>
            .wc-resource-filter small{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:22px;border-radius:999px;background:rgba(15,23,42,.08);padding:0 7px;font-size:11px;font-weight:900}
            .wc-resource-filter.is-active small{background:rgba(255,255,255,.18);color:inherit}
        </style>
    @endpush
    @push('scripts')
        <script>
            document.addEventListener('change', function(event){
                const input = event.target instanceof Element ? event.target.closest('.wc-resource-filter input[type="checkbox"]') : null;
                if (!input) return;
                input.form?.requestSubmit();
            });
        </script>
    @endpush
@endonce
