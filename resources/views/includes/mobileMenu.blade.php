<div id="mobileMenu" class="sidebar-menu">
    <div class="sidebar-brand">
        <a class="sidebar-brand-link" href="{{ route('dashboard.index') }}">
            <span class="sidebar-brand-logo sidebar-brand-logo-image">
                <img src="/admin/images/logo.png" alt="WebTools logo">
            </span>
            <span class="sidebar-brand-text">WebTools</span>
        </a>
    </div>

    <div class="sidebar-section-label">Navigation</div>

    <div class="sidebar-nav-list">
        <div class="sidebar-nav-item @if(Route::is('dashboard.index')) active-link @endif">
            <a class="nav-link uppercase" href="{{ route('dashboard.index') }}">
                <div class="sidebar-nav-icon"><i class="fa-solid fa-chart-pie @if(Route::is('dashboard.index')) active-link-icon @endif"></i></div>
                <div class="sideMenuText">{{ __('menu.dashboard') }}</div>
            </a>
        </div>

        @if(auth()->user()->id == 1)
            <div class="sidebar-nav-item @if(Route::is('administration.index')) active-link @endif">
                <a class="nav-link uppercase" href="{{ route('administration.index') }}">
                    <div class="sidebar-nav-icon"><i class="fa-solid fa-people-roof @if(Route::is('administration.index')) active-link-icon @endif"></i></div>
                    <div class="sideMenuText">{{ __('menu.admin') }}</div>
                </a>
            </div>
            <div class="sidebar-nav-item @if(Route::is('finance.index')) active-link @endif">
                <a class="nav-link uppercase" href="{{ route('finance.index') }}">
                    <div class="sidebar-nav-icon"><i class="fa-solid fa-chart-line @if(Route::is('finance.index')) active-link-icon @endif"></i></div>
                    <div class="sideMenuText">{{ __('menu.finance') }}</div>
                </a>
            </div>
            <div class="sidebar-nav-item @if(Route::is('marketing.index')) active-link @endif">
                <a class="nav-link uppercase" href="{{ route('marketing.index') }}">
                    <div class="sidebar-nav-icon"><i class="fa-solid fa-bullhorn @if(Route::is('marketing.index')) active-link-icon @endif"></i></div>
                    <div class="sideMenuText">{{ __('menu.marketing') }}</div>
                </a>
            </div>
            <div style="display: none;" class="sidebar-nav-item @if(Route::is('customerSupport.index')) active-link @endif">
                <a class="nav-link uppercase" href="{{ route('customerSupport.index') }}">
                    <div class="sidebar-nav-icon"><i class="fa-solid fa-headset @if(Route::is('customerSupport.index')) active-link-icon @endif"></i></div>
                    <div class="sideMenuText">{{ __('menu.customer support') }}</div>
                </a>
            </div>
        @endif

        @if(auth()->user()->id == 2)
            <div class="sidebar-nav-item @if(Route::is('budget.index')) active-link @endif">
                <a class="nav-link uppercase" href="{{ route('budget.index') }}">
                    <div class="sidebar-nav-icon"><i class="fa-solid fa-euro-sign @if(Route::is('budget.index')) active-link-icon @endif"></i></div>
                    <div class="sideMenuText">Budget</div>
                </a>
            </div>
        @endif

        <div class="sidebar-nav-item @if(Route::is('tasks.index')) active-link @endif">
            <a class="nav-link uppercase" href="{{ route('tasks.index') }}">
                <div class="sidebar-nav-icon"><i class="fa-solid fa-box @if(Route::is('tasks.index')) active-link-icon @endif"></i></div>
                <div class="sideMenuText">{{ __('menu.tasks') }}</div>
            </a>
        </div>

        <div class="sidebar-nav-item @if(Route::is('sales.index')) active-link @endif">
            <a class="nav-link uppercase" href="{{ route('sales.index') }}">
                <div class="sidebar-nav-icon"><i class="fa-solid fa-eur @if(Route::is('sales.index')) active-link-icon @endif"></i></div>
                <div class="sideMenuText">{{ __('menu.sales') }}</div>
            </a>
        </div>

        @if(auth()->user()->id == 1)
            <div class="sidebar-nav-item @if(Route::is('web.index')) active-link @endif">
                <a class="nav-link uppercase" href="{{ route('web.index') }}">
                    <div class="sidebar-nav-icon"><i class="fa-solid fa-code @if(Route::is('web.index')) active-link-icon @endif"></i></div>
                    <div class="sideMenuText">{{ __('menu.webmaster') }}</div>
                </a>
            </div>
        @endif
    </div>

    <div class="sidebar-section-label sidebar-section-bottom">Session</div>
    <div class="sidebar-nav-list">
        <div class="sidebar-nav-item sidebar-nav-item-logout">
            <a class="nav-link uppercase" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <div class="sidebar-nav-icon"><i class="fa-solid fa-sign-out"></i></div>
                <div class="sideMenuText">{{ __('menu.logout') }}</div>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
</div>

<script>
function openMobileMenu(closed){
    if(closed==1){
        $('#openMobileMenuTrigger').replaceWith('<div id="openMobileMenuTrigger" class="mobile-sidebar-toggle" onclick="openMobileMenu(0)"><i class="fa-solid fa-chevron-up"></i></div>');
        $('#mainMenuMobileContainer').addClass('is-open');
    }else{
        $('#openMobileMenuTrigger').replaceWith('<div id="openMobileMenuTrigger" class="mobile-sidebar-toggle" onclick="openMobileMenu(1)"><i class="fa-solid fa-chevron-down"></i></div>');
        $('#mainMenuMobileContainer').removeClass('is-open');
    }
}
</script>
