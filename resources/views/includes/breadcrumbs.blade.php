<div class="breadcrumbs-shell">
    <div class="breadcrumbs-main">

        <button type="button" class="navbar-brand sideMenuLogo mobile-menu-toggle" data-mobile-menu-toggle>
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="breadcrumbs-copy">
            <h3>{{ is_string($pageTitle ?? null) ? $pageTitle : __('breadcrumbs.home.index') }}</h3>

            @php
                $hasBreadcrumbs = isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs) > 0;
            @endphp

            @if($hasBreadcrumbs)
                <ul>
                    @foreach ($breadcrumbs as $breadcrumb)
                        @if(!$loop->first)
                            <li class="breadcrumbs-separator">
                                <i class="fa fa-chevron-right"></i>
                            </li>
                        @endif

                        @php
                            $label = $breadcrumb['label'] ?? '';
                            $isHome = in_array($label, ['Dashboard', 'Home'], true);
                        @endphp

                        <li>
                            @if(!empty($breadcrumb['url']) && !$loop->last)
                                <a href="{{ $breadcrumb['url'] }}">
                            @endif

                            @if($isHome)
                                <i class="fa-solid fa-house"></i>
                            @else
                                {{ $label }}
                            @endif

                            @if(!empty($breadcrumb['url']) && !$loop->last)
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

    </div>
</div>
