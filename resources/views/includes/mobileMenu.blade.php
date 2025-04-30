<div id="mobileMenu">
    <div>
        <div>
            <div class="text-center mobileMenuItem @if(Route::is('dashboard.index') ) active-link @endif"> 
                <a class="nav-link uppercase" href="{{ route('dashboard.index') }}"> 
                    <div><i class="fa-solid fa-chart-pie @if(Route::is('dashboard.index') ) active-link-icon @endif" style="font-size: 40px;"></i></div>
                    <div class="sideMenuText">{{ __('menu.dashboard') }}</div>
                </a> 
            </div>
            @if(auth()->user()->id == 1)
            <div class="text-center mobileMenuItem @if(Route::is('administration.index') ) active-link @endif"> 
                <a class="nav-link uppercase" href="{{ route('administration.index') }}">
                    <div><i class="fa-solid fa-people-roof @if(Route::is('administration.index') ) active-link-icon @endif" style="font-size: 40px;"></i></div>
                    <div class="sideMenuText"> {{ __('menu.admin') }}</div>
                </a> 
            </div>
            @endif
            @if(auth()->user()->id == 1)
            <div class="text-center mobileMenuItem @if(Route::is('finance.index') ) active-link @endif"> 
                <a class="nav-link uppercase" href="{{ route('finance.index') }}">
                    <div><i class="fa-solid fa-chart-line @if(Route::is('finance.index') ) active-link-icon @endif" style="font-size: 40px;"></i></div>
                    <div class="sideMenuText"> {{ __('menu.finance') }}</div>
                </a> 
            </div>
            @endif
            <div class="text-center mobileMenuItem @if(Route::is('sales.index') ) active-link @endif"> 
                <a class="nav-link uppercase" href="{{ route('sales.index') }}">
                    <div><i class="fa-solid fa-eur @if(Route::is('sales.index') ) active-link-icon @endif" style="font-size: 40px;"></i></div>
                    <div class="sideMenuText"> {{ __('menu.sales') }}</div>
                </a>
            </div>
            @if(auth()->user()->id == 1)
            <div class="text-center mobileMenuItem @if(Route::is('tasks.index') ) active-link @endif"> 
                <a class="nav-link uppercase" href="{{ route('tasks.index') }}">
                    <div><i class="fa-solid fa-box @if(Route::is('tasks.index') ) active-link-icon @endif" style="font-size: 40px;"></i></div>
                    <div class="sideMenuText"> {{ __('menu.tasks') }}</div>
                </a> 
            </div>
            @endif
            @if(auth()->user()->id == 1)
            <div class="text-center mobileMenuItem @if(Route::is('marketing.index') ) active-link @endif"> 
                <a class="nav-link uppercase" href="{{ route('marketing.index') }}">
                    <div><i class="fa-solid fa-bullhorn @if(Route::is('marketing.index') ) active-link-icon @endif" style="font-size: 40px;"></i></div>
                    <div class="sideMenuText"> {{ __('menu.marketing') }}</div>
                </a> 
            </div>
            @endif
            @if(auth()->user()->id == 1)
            <div class="text-center mobileMenuItem @if(Route::is('customerSupport.index') ) active-link @endif"> 
                <a class="nav-link uppercase" href="{{ route('customerSupport.index') }}">
                    <div><i class="fa-solid fa-headset @if(Route::is('customerSupport.index') ) active-link-icon @endif" style="font-size: 40px;"></i></div>
                    <div class="sideMenuText"> {{ __('menu.customer support') }}</div>
                </a>
            </div>
            @endif
            @if(auth()->user()->id == 1)
            <div class="text-center mobileMenuItem @if(Route::is('web.index') ) active-link @endif"> 
                <a class="nav-link uppercase" href="{{ route('web.index') }}">
                    <div><i class="fa-solid fa-code @if(Route::is('web.index') ) active-link-icon @endif" style="font-size: 40px;"></i></div>
                    <div class="sideMenuText"> {{ __('menu.webmaster') }}</div>
                </a>
            </div>
            @endif
            <div class="text-center mobileMenuItem"> 
                <a class="nav-link uppercase" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <div><i class="fa-solid fa-sign-out @if(Route::is('logout') ) active-link-icon @endif" style="font-size: 40px;"></i></div>
                    <div class="sideMenuText"> {{ __('menu.logout') }}</div>
                </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>

<style>

#openMobileMenuContainer{

    display: none;

}

#openMobileMenuTrigger{ text-align: center; }
#openMobileMenuTrigger:hover{ text-align: center; background-color: #ccc; }

.mobile{ display: none; }
.desktop{ display: block; }

#mainContentView{ margin: 0 10px; }
@media (max-width: 768px) {

    #mainMenuMobileContainer{ float: none; margin-top: 10px; display: ruby;}
    #mainMenuMobile{display: none; margin-top: 20px; width: 100%;}
    
    #openMobileMenuContainer{ display: block; margin-top: 5px;}
    #openMobileMenuTrigger{ padding: 10px 0 5px 0; border-top: 1px solid #bbb;}
    .mobileMenuItem { width: 33%;float: left;height: 85px; padding: 15px; border: 1px solid #ddd; }

    .mobile{ display: block; }
    .desktop{ display: none; }

}

</style>

<script>

function openMobileMenu(closed){
    
    if(closed==1){
        $('#openMobileMenuTrigger').replaceWith('<div id="openMobileMenuTrigger" onclick="openMobileMenu(0)"><i class="fa-solid fa-chevron-up"></i></div>')
        $('#mainMenuMobileContainer').css('height', '250px');
        $('#mainMenuMobileContainer').css('display', 'contents');
    }else{
        $('#openMobileMenuTrigger').replaceWith('<div id="openMobileMenuTrigger" onclick="openMobileMenu(1)"><i class="fa-solid fa-chevron-down"></i></div>')
        $('#mainMenuMobileContainer').css('height', '0px');
    }

    $('#mainMenuMobile').toggle();
}
</script>

