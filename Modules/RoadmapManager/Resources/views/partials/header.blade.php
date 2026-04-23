@php
    $hasBreadcrumbs = ($showBreadcrumbs ?? true) && !empty($breadcrumbs ?? []);
    $pageHeading = $pageTitle ?? 'Roadmap';
@endphp

<div class="rm-page-header">
    @if($hasBreadcrumbs)
        <div class="rm-breadcrumbs">
            @foreach(($breadcrumbs ?? []) as $index => $breadcrumb)
                @if($index > 0)
                    <span class="rm-breadcrumbs__sep">/</span>
                @endif

                @if(!empty($breadcrumb['url']) && $index < count($breadcrumbs) - 1)
                    <a href="{{ $breadcrumb['url'] }}" class="rm-breadcrumbs__link">{{ $breadcrumb['label'] }}</a>
                @else
                    <span class="rm-breadcrumbs__current">{{ $breadcrumb['label'] }}</span>
                @endif
            @endforeach
        </div>
    @endif

    <div class="rm-page-header__main">
        <div class="rm-page-header__identity">
            <span class="rm-page-header__icon"><i class="fa-solid fa-road"></i></span>
            <div>
                <h1 class="rm-page-header__title">{{ $pageHeading }}</h1>
                @if(!empty($pageTitleSuffix ?? null))
                    <p class="rm-page-header__subtitle">{{ $pageTitleSuffix }}</p>
                @endif
            </div>
        </div>

        @if(!empty($actions ?? []))
            <div class="rm-page-header__actions">
                <div class="lsg-page-actions">
                    @foreach($actions as $action)
                        @php
                            $label = $action['label'] ?? $action['name'] ?? ucfirst($action['key'] ?? 'Action');
                            $icon = $action['icon'] ?? null;
                            $class = $action['class'] ?? 'lsg-action-btn lsg-action-btn--primary';
                            $url = $action['url'] ?? null;
                            $method = strtoupper($action['method'] ?? 'GET');
                            $confirm = $action['confirm'] ?? null;
                            $type = $action['type'] ?? 'link';
                            $attrs = '';
                        @endphp

                        @if($url && $method === 'GET' && $type !== 'delete')
                            <a href="{{ $url }}" class="{{ $class }}">
                                @if($icon)<span class="lsg-action-btn__icon"><i class="{{ $icon }}"></i></span>@endif
                                <span class="lsg-action-btn__label">{{ $label }}</span>
                            </a>
                        @elseif($url)
                            <form method="POST" action="{{ $url }}" class="lsg-action-form" @if(is_string($confirm) && $confirm !== '') onsubmit="return confirm('{{ $confirm }}')" @endif>
                                @csrf
                                @if($method !== 'POST')
                                    @method($method)
                                @endif
                                <button type="{{ ($action['submit_type'] ?? null) === 'submit' ? 'submit' : 'submit' }}" class="{{ $class }}" @if(!empty($action['form'])) form="{{ $action['form'] }}" formaction="{{ $url }}" @endif>
                                    @if($icon)<span class="lsg-action-btn__icon"><i class="{{ $icon }}"></i></span>@endif
                                    <span class="lsg-action-btn__label">{{ $label }}</span>
                                </button>
                            </form>
                        @elseif(($action['submit_type'] ?? null) === 'submit')
                            <button type="submit" class="{{ $class }}" @if(!empty($action['form'])) form="{{ $action['form'] }}" @endif>
                                @if($icon)<span class="lsg-action-btn__icon"><i class="{{ $icon }}"></i></span>@endif
                                <span class="lsg-action-btn__label">{{ $label }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
