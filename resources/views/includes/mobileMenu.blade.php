@php
    $menuItems = [
        ['route' => 'administration.index', 'label' => 'Admin', 'icon' => 'fa-solid fa-people-roof'],
        ['route' => 'web.index', 'label' => 'Webmaster', 'icon' => 'fa-solid fa-code'],
        ['route' => 'sales.index', 'label' => 'Sales', 'icon' => 'fa-solid fa-chart-line'],
        ['route' => 'finance.index', 'label' => 'Finance', 'icon' => 'fa-solid fa-wallet'],
        ['route' => 'marketing.index', 'label' => 'Marketing', 'icon' => 'fa-solid fa-bullhorn'],
        ['route' => 'customerSupport.index', 'label' => 'Support', 'icon' => 'fa-solid fa-headset'],
        ['route' => 'hr.index', 'label' => 'HR', 'icon' => 'fa-solid fa-user-group'],
    ];
@endphp

@php
    $menuItemsExtra = [
        ['route' => 'family.index', 'label' => 'Family', 'icon' => 'fa-solid fa-hands-holding-child'],
        ['route' => 'webCatalogue.index', 'label' => 'Web Catalogue', 'icon' => 'fa-solid fa-book-open'],
        ['route' => 'multiStore.index', 'label' => "Store's", 'icon' => 'fa-solid fa-store'],
    ];
@endphp

<div id="mobileMenu" class="sidebar-menu">
    <div class="sidebar-brand">
        <a class="sidebar-brand-link" href="{{ route('dashboard.index') }}">
            <span class="sidebar-brand-logo sidebar-brand-logo-image">
                <img src="/admin/images/logo.png" alt="WebTools logo">
            </span>
            <span class="sidebar-brand-text">WebTools</span>
        </a>
    </div>

    <div class="sidebar-nav-list sidebar-nav-main-list">
        @foreach($menuItems as $item)
            @if(Route::has($item['route']))
                <div class="sidebar-nav-item {{ Route::is($item['route']) ? 'active-link' : '' }}">
                    <a class="nav-link uppercase" href="{{ route($item['route']) }}">
                        <div class="sidebar-nav-icon"><i class="{{ $item['icon'] }} {{ Route::is($item['route']) ? 'active-link-icon' : '' }}"></i></div>
                        <div class="sideMenuText">{{ $item['label'] }}</div>
                    </a>
                </div>
            @endif
        @endforeach
    </div>

    <div style="margin-top: 10px;">OTHER AREAS</div>
    <div class="sidebar-nav-list sidebar-nav-main-list">
        @foreach($menuItemsExtra as $item)
            @if(Route::has($item['route']))
                <div class="sidebar-nav-item {{ Route::is($item['route']) ? 'active-link' : '' }}">
                    <a class="nav-link uppercase" href="{{ route($item['route']) }}">
                        <div class="sidebar-nav-icon"><i class="{{ $item['icon'] }} {{ Route::is($item['route']) ? 'active-link-icon' : '' }}"></i></div>
                        <div class="sideMenuText">{{ $item['label'] }}</div>
                    </a>
                </div>
            @endif
        @endforeach
    </div>

    <div class="sidebar-footer-tools">
        <button type="button" class="sidebar-footer-tool" data-sidebar-toggle aria-label="Condensar menu" title="Condensar menu">
            <i class="fa-solid fa-bars-staggered"></i>
        </button>

        <button type="button" class="sidebar-footer-tool" data-theme-toggle aria-label="Alternar tema" title="Alternar tema">
            <i class="fa-solid fa-circle-half-stroke"></i>
        </button>

        <a class="sidebar-footer-tool" href="{{ route('logout') }}" aria-label="Logout" title="Logout"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fa-solid fa-right-from-bracket"></i>
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </div>
</div>
