<div class="breadcrumbs-shell">
    <div class="breadcrumbs-main">

        <button type="button" class="navbar-brand sideMenuLogo mobile-menu-toggle" data-mobile-menu-toggle>
            <i class="fa-solid fa-bars"></i>
        </button>

        <div class="breadcrumbs-copy">
            <h3>
                {{ __('breadcrumbs.' . ($pageTitle ?? 'home')) }}
            </h3>

            @php
                $hasBreadcrumbs = isset($breadcrumbs) && is_array($breadcrumbs) && count($breadcrumbs) > 0;

                $showTrail = true;

                if ($hasBreadcrumbs && count($breadcrumbs) === 1) {
                    $single = $breadcrumbs[0]['label'] ?? null;
                    $title  = $pageTitle ?? 'home';

                    if ($single === $title) {
                        $showTrail = false;
                    }
                }
            @endphp

            @if($hasBreadcrumbs && $showTrail)
                <ul>
                    @foreach ($breadcrumbs as $breadcrumb)
                        @if(!$loop->first)
                            <li class="breadcrumbs-separator">
                                <i class="fa fa-chevron-right"></i>
                            </li>
                        @endif

                        <li>
                            @if(!empty($breadcrumb['url']) && !$loop->last)
                                <a href="{{ $breadcrumb['url'] }}">
                            @endif

                            @if(isset($breadcrumb['translate']) && $breadcrumb['translate'])
                                {{ __('breadcrumbs.' . $breadcrumb['label'], $breadcrumb['params'] ?? []) }}
                            @else
                                {{ $breadcrumb['label'] ?? '' }}
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