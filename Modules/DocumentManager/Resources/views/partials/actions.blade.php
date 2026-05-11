@php
    $routeName = Route::currentRouteName();
    $actions = config("documentmanager.actions.$routeName", config('documentmanager.actions.document-manager.dashboard', []));
@endphp

<div class="dms-actions">
    @foreach($actions as $action)
        @if(Route::has($action['route']))
            @php
                $routeParams = $action['params'] ?? request()->route()?->parameters() ?? [];
            @endphp
            <a href="{{ route($action['route'], $routeParams) }}" class="btn btn-{{ $action['class'] ?? 'outline-primary' }}">
                <i class="{{ $action['icon'] ?? 'fa-solid fa-circle' }}"></i>
                <span>{{ $action['label'] }}</span>
            </a>
        @endif
    @endforeach
</div>
