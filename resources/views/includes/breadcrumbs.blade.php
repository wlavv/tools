@php
    $isMobile = preg_match('/Mobile|Android|iP(hone|od|ad)|IEMobile|BlackBerry|Opera Mini/i', request()->header('User-Agent'));
@endphp

<div class="breadcrumbs-shell">
    <div class="breadcrumbs-main">
        <button type="button" class="navbar-brand sideMenuLogo mobile-menu-toggle" onclick="openMobileMenu($('#mainMenuMobileContainer').hasClass('is-open') ? 0 : 1)">
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="breadcrumbs-copy" @if($isMobile) style="overflow-x:auto; width:100%;" @endif>
            <h3 id="breadcrumbs_h3">
                @if(isset($breadcrumb['params']))
                    {{ __('breadcrumbs.' . Route::currentRouteName(), $breadcrumb['params']) }}
                @else
                    {{ __('breadcrumbs.' . Route::currentRouteName()) }}
                @endif
            </h3>

            <ul id="breadcrumbs_ul">
                <li><a href="{{route('dashboard.index')}}">{{ __('breadcrumbs.home') }}</a></li>
                @if(isset($breadcrumbs[0]))
                    <li class="breadcrumbs-separator"><span><i class="fa fa-chevron-right"></i></span></li>
                @endif
                @if(isset($breadcrumbs) && (count($breadcrumbs) > 0))
                    @foreach ($breadcrumbs as $key => $breadcrumb)
                        @if(isset($breadcrumb['no_translation']) && ($breadcrumb['no_translation'] == 1))
                            <li>
                                <a href="#">
                                    @if(isset($breadcrumb['params']))
                                        {{ __('breadcrumbs.' . $breadcrumb['name'], $breadcrumb['params']) }}
                                    @else
                                        {{ __('breadcrumbs.' . $breadcrumb['name']) }}
                                    @endif
                                </a>
                            </li>
                        @else
                            <li>
                                <a href="{{$breadcrumb['url']}}">
                                    @if(isset($breadcrumb['params']))
                                        {{ __('breadcrumbs.' . $breadcrumb['name'], $breadcrumb['params']) }}
                                    @else
                                        {{ __('breadcrumbs.' . $breadcrumb['name']) }}
                                    @endif
                                </a>
                            </li>
                        @endif
                        @if(isset($breadcrumbs[$key+1]))
                            <li class="breadcrumbs-separator"><span><i class="fa fa-chevron-right"></i></span></li>
                        @endif
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>
