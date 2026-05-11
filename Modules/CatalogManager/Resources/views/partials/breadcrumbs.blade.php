@php
    $routeName = Route::currentRouteName();
    $breadcrumbsConfig = config('catalogmanager.breadcrumbs', []);

    $trail = [];
    $current = $routeName;

    while ($current && isset($breadcrumbsConfig[$current])) {
        array_unshift($trail, [
            'route' => $current,
            'label' => $breadcrumbsConfig[$current]['label'] ?? $current,
        ]);

        $current = $breadcrumbsConfig[$current]['parent'] ?? null;
    }
@endphp

@if(count($trail))
    <div class="catalog-lsg-breadcrumbs">
        @foreach($trail as $index => $crumb)
            @if($index > 0)
                <span>/</span>
            @endif

            @if($index < count($trail) - 1 && Route::has($crumb['route']))
                <a href="{{ route($crumb['route']) }}">{{ $crumb['label'] }}</a>
            @else
                <span>{{ $crumb['label'] }}</span>
            @endif
        @endforeach
    </div>
@endif
