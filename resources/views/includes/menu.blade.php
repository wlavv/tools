@php
    $routeAccess = app(\Modules\PermissionRoleManager\Services\RoutePermissionAccessService::class);
    $menuCan = fn(string $routeName): bool => Route::has($routeName)
        && $routeAccess->canAccessRouteName(auth()->id(), $routeName);
    $firstAllowedRoute = function (array $routes) use ($menuCan): ?string {
        foreach ($routes as $route) {
            if ($menuCan($route)) {
                return $route;
            }
        }

        return null;
    };

    $menuItems = [
        ['route' => 'administration.index', 'label' => 'Admin', 'icon' => 'fa-solid fa-people-roof', 'active' => ['administration.*']],
        ['route' => 'web.index', 'label' => 'Webmaster', 'icon' => 'fa-solid fa-code', 'active' => ['web.*']],
        ['route' => 'sales.index', 'label' => 'Sales', 'icon' => 'fa-solid fa-chart-line', 'active' => ['sales.*']],
        ['route' => 'finance.index', 'label' => 'Finance', 'icon' => 'fa-solid fa-wallet', 'active' => ['finance.*']],
        ['route' => 'marketing.index', 'label' => 'Marketing', 'icon' => 'fa-solid fa-bullhorn', 'active' => ['marketing.*']],
        ['route' => 'customerSupport.index', 'label' => 'Support', 'icon' => 'fa-solid fa-headset', 'active' => ['customerSupport.*']],
        ['route' => 'hr.index', 'label' => 'HR', 'icon' => 'fa-solid fa-user-group', 'active' => ['hr.*']],
        ['route' => $firstAllowedRoute(['purchasing.index', 'erp.dashboard']), 'label' => 'Purchasing', 'icon' => 'fa-solid fa-cart-flatbed', 'active' => ['purchasing.*', 'erp.*']],
    ];
@endphp
@php
    $menuItemsExtra = [
        ['route' => 'family.index', 'label' => 'Family', 'icon' => 'fa-solid fa-hands-holding-child', 'active' => ['family.*']],
        ['route' => $firstAllowedRoute(['lsg.index', 'multiStore.index', 'catalog-manager.stores.index', 'webcatalogue.index']), 'label' => 'LSG', 'icon' => 'fa-solid fa-building', 'active' => ['lsg.*', 'multiStore.*', 'catalog-manager.stores.*', 'webcatalogue.*']],
    ];
@endphp
<div id="mobileMenu" class="sidebar-menu">
    <div class="sidebar-brand">
        <a class="sidebar-brand-link" href="{{ route('dashboard.index') }}">
            <span class="sidebar-brand-logo sidebar-brand-logo-image"> <img src="/admin/images/logo.png" alt="WebTools logo"> </span>
            <span class="sidebar-brand-text">WebTools</span>
        </a>
    </div>

    <div class="sidebar-nav-list sidebar-nav-main-list">
        @foreach($menuItems as $item)
            @if($item['route'] && $menuCan($item['route']))
                @php $isActive = Route::is(...($item['active'] ?? [$item['route']])); @endphp
                <div class="sidebar-nav-item {{ $isActive ? 'active-link' : '' }}">
                    <a class="nav-link uppercase" href="{{ route($item['route']) }}">
                        <div class="sidebar-nav-icon"><i class="{{ $item['icon'] }} {{ $isActive ? 'active-link-icon' : '' }}"></i></div>
                        <div class="sideMenuText">{{ $item['label'] }}</div>
                    </a>
                </div>
            @endif
        @endforeach
    </div>

    <div class="sidebar-section-label">OTHERS</div>
    <div class="sidebar-nav-list sidebar-nav-main-list">
        @foreach($menuItemsExtra as $item)
            @if($item['route'] && $menuCan($item['route']))
                @php $isActive = Route::is(...($item['active'] ?? [$item['route']])); @endphp
                <div class="sidebar-nav-item {{ $isActive ? 'active-link' : '' }}">
                    <a class="nav-link uppercase" href="{{ route($item['route']) }}">
                        <div class="sidebar-nav-icon"><i class="{{ $item['icon'] }} {{ $isActive ? 'active-link-icon' : '' }}"></i></div>
                        <div class="sideMenuText">{{ $item['label'] }}</div>
                    </a>
                </div>
            @endif
        @endforeach
    </div>
</div>
