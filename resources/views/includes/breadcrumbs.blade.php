<div class="breadcrumbs-shell">
    <div class="breadcrumbs-main">

        <button type="button" class="navbar-brand sideMenuLogo mobile-menu-toggle" data-mobile-menu-toggle>
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="breadcrumbs-copy">
            <h3>
                {{ __('breadcrumbs.' . ($pageTitle ?? 'home')) }}
            </h3>

            <ul>
                <li>
                    <a href="{{ route('dashboard.index') }}">
                        {{ __('breadcrumbs.home') }}
                    </a>
                </li>

                @if ( isset($breadcrumbs))
                    @foreach ($breadcrumbs as $breadcrumb)
                        <li class="breadcrumbs-separator">
                            <i class="fa fa-chevron-right"></i>
                        </li>

                        <li>
                            @if($breadcrumb['url'])
                                <a href="{{ $breadcrumb['url'] }}">
                            @endif

                            @if(isset($breadcrumb['translate']) && $breadcrumb['translate'])
                                {{ __('breadcrumbs.' . $breadcrumb['label'], $breadcrumb['params'] ?? []) }}
                            @else
                                {{ $breadcrumb['label'] ?? '' }}
                            @endif

                            @if($breadcrumb['url'])
                                </a>
                            @endif
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>

    </div>
</div>
