@php
    $links = [
        ['route' => 'investments.index', 'label' => 'Overview', 'icon' => 'fa-solid fa-chart-line'],
        ['route' => 'investments.positions.index', 'label' => 'Positions', 'icon' => 'fa-solid fa-arrow-trend-up'],
        ['route' => 'investments.assets.index', 'label' => 'Assets', 'icon' => 'fa-solid fa-layer-group'],
        ['route' => 'investments.broker_accounts.index', 'label' => 'Broker Accounts', 'icon' => 'fa-solid fa-building-columns'],
    ];
@endphp

<nav class="investments-nav">
    @foreach($links as $link)
        <a href="{{ route($link['route']) }}" class="{{ request()->routeIs($link['route']) ? 'is-active' : '' }}">
            <i class="{{ $link['icon'] }}" aria-hidden="true"></i>
            <span>{{ $link['label'] }}</span>
        </a>
    @endforeach
</nav>
