@php
    $routeName = Route::currentRouteName();
    $items = [];
    $map = config('documentmanager.breadcrumbs', []);
    $cursor = $routeName;

    while ($cursor && isset($map[$cursor])) {
        array_unshift($items, [
            'label' => $map[$cursor]['label'],
            'route' => $cursor,
        ]);

        $cursor = $map[$cursor]['parent'] ?? null;
    }
@endphp

<div class="dms-breadcrumbs">
    <div>
        <span class="dms-eyebrow">LSG Operating Knowledge System</span>
        <h1>{{ config("documentmanager.page_titles.$routeName", 'Document Manager') }}</h1>
    </div>

    <ol>
        @foreach($items as $item)
            <li>
                @if(!$loop->last && Route::has($item['route']))
                    <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                @else
                    <span>{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</div>
