@php
    $selectedResources = (array) request()->query('resources', []);
@endphp
<form class="wc-filter-panel" method="GET">
    <div class="wc-filter-main">
        <div class="wc-filter-field wc-filter-search">
            <label>Search</label>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Name, reference, SKU, brand...">
        </div>
        <div class="wc-filter-field">
            <label>Brand</label>
            <select name="brand">
                <option value="">All brands</option>
                @foreach(($filters['brands'] ?? []) as $brand)
                    <option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>
                @endforeach
            </select>
        </div>
        <div class="wc-filter-field">
            <label>Category</label>
            <select name="category">
                <option value="">All categories</option>
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
                <span>{{ $option['label'] }}</span>
            </label>
        @endforeach
    </div>

    <div class="wc-filter-actions">
        <button class="wc-btn wc-btn-primary" type="submit"><i class="fa-solid fa-filter"></i> Apply filters</button>
        <a class="wc-btn" href="{{ url()->current() }}"><i class="fa-solid fa-rotate-left"></i> Clear</a>
    </div>
</form>
