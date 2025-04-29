<a class="navbar-brand sideMenuLogo" href="https://www.webtools-manager.com/" target="_blank" style="padding: 0;margin: 0 20px;display: inline-flex;">
    <img src="/admin/images/logo.png" style="width: 75px; @guest display: none; @endif">
</a>
<div style="display: inline-block;padding: 0;text-align: left;">
        <div>
            <h3 style="padding-left: 20px;text-transform: uppercase;">                            
                @if( isset($breadcrumb['params']))
                    {{ __('breadcrumbs.' . Route::currentRouteName(), $breadcrumb['params']) }}                            
                @else             
                    {{ __('breadcrumbs.' . Route::currentRouteName()) }}
                @endif
            </h3>
        </div>
    <ul style="list-style: none;padding-left: 20px; margin-bottom: 0; display: inline-flex;">
        <li> <a href="{{route('dashboard.index')}}" style="text-transform: uppercase;color: #666;text-decoration: none;">{{ __('breadcrumbs.home') }} </a> </li>
        @if(isset($breadcrumbs[0]))<li> <span> <i class="fa fa-chevron-right" style="color: dodgerblue; margin: 0 10px;"></i> </span> </li> @endif
        @if(isset($breadcrumbs) && (count($breadcrumbs) > 0) )
            @foreach ($breadcrumbs as $key => $breadcrumb)
                @if( isset($breadcrumb['no_translation']) && ($breadcrumb['no_translation'] == 1))
                    <li>
                        <a href="#" style="text-transform: uppercase;color: #666;text-decoration: none;">
                            @if( isset($breadcrumb['params']))
                                {{ __('breadcrumbs.' . $breadcrumb['name'], $breadcrumb['params']) }}                            
                            @else
                                {{ __('breadcrumbs.' . $breadcrumb['name']) }}
                            @endif                        
                        </a> 
                    </li>
                @else
                    <li> 
                        <a href="{{$breadcrumb['url']}}" style="text-transform: uppercase;color: #666;text-decoration: none;">
                            @if( isset($breadcrumb['params']))
                                {{ __('breadcrumbs.' . $breadcrumb['name'], $breadcrumb['params']) }}                            
                            @else
                                {{ __('breadcrumbs.' . $breadcrumb['name']) }}
                            @endif
                        </a> 
                    </li>
                @endif
                @if(isset($breadcrumbs[$key+1]))<li> <span> <i class="fa fa-chevron-right" style="color: dodgerblue; margin: 0 10px;"></i> </span> </li>@endif
            @endforeach
        @endif
    </ul>
</div>
<div style="float: right;"> @include('includes.action') </div>
<div id="openMobileMenuContainer">
    <div id="openMobileMenuTrigger" onclick="openMobileMenu(1)"><i class="fa-solid fa-chevron-down"></i></div>
</div>