@php
    $moduleBreadcrumbs = $breadcrumbs ?? $breadCrumbs ?? $breadcrumbItems ?? [];
    $moduleActions = $actions ?? $pageActions ?? $headerActions ?? $actionList ?? [];
@endphp

@if(!empty($moduleBreadcrumbs) || !empty($moduleActions))
    <div class="lsg-module-header-fallback mb-3">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2">
            @if(!empty($moduleBreadcrumbs))
                <nav aria-label="breadcrumb" class="lsg-module-breadcrumbs">
                    <ol class="breadcrumb mb-0">
                        @foreach($moduleBreadcrumbs as $breadcrumb)
                            @php
                                $label = $breadcrumb['label'] ?? $breadcrumb['name'] ?? '';
                                $url = $breadcrumb['url'] ?? $breadcrumb['href'] ?? null;
                                $active = (bool)($breadcrumb['active'] ?? $loop->last);
                            @endphp

                            <li class="breadcrumb-item {{ $active ? 'active' : '' }}" @if($active) aria-current="page" @endif>
                                @if(!$active && !empty($url))
                                    <a href="{{ $url }}">
                                        @if(!empty($breadcrumb['icon']))
                                            <i class="{{ $breadcrumb['icon'] }} me-1"></i>
                                        @endif
                                        {{ $label }}
                                    </a>
                                @else
                                    @if(!empty($breadcrumb['icon']))
                                        <i class="{{ $breadcrumb['icon'] }} me-1"></i>
                                    @endif
                                    {{ $label }}
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            @endif

            @if(!empty($moduleActions))
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end lsg-module-actions">
                    @foreach($moduleActions as $action)
                        @php
                            $label = $action['label'] ?? $action['name'] ?? '';
                            $url = $action['url'] ?? $action['href'] ?? null;
                            $class = $action['class'] ?? 'lsg-action-btn lsg-action-btn--primary';
                            $method = strtoupper($action['method'] ?? 'GET');
                        @endphp

                        @if(!empty($url))
                            @if($method === 'GET')
                                <a href="{{ $url }}" class="{{ $class }}">
                                    @if(!empty($action['icon']))
                                        <i class="{{ $action['icon'] }} me-1"></i>
                                    @endif
                                    {{ $label }}
                                </a>
                            @else
                                <form method="POST" action="{{ $url }}" class="d-inline">
                                    @csrf
                                    @if(!in_array($method, ['POST']))
                                        @method($method)
                                    @endif
                                    <button type="submit" class="{{ $class }}">
                                        @if(!empty($action['icon']))
                                            <i class="{{ $action['icon'] }} me-1"></i>
                                        @endif
                                        {{ $label }}
                                    </button>
                                </form>
                            @endif
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endif
