@php
    $items = [
        ['route' => 'document-manager.dashboard', 'label' => 'Dashboard', 'icon' => 'fa-solid fa-chart-line'],
        ['route' => 'document-manager.documents.index', 'label' => 'Explorer', 'icon' => 'fa-solid fa-folder-tree'],
        ['route' => 'document-manager.search.index', 'label' => 'Search', 'icon' => 'fa-solid fa-magnifying-glass'],
        ['route' => 'document-manager.workspaces.index', 'label' => 'Workspaces', 'icon' => 'fa-solid fa-layer-group'],
        ['route' => 'document-manager.folders.index', 'label' => 'Folders', 'icon' => 'fa-solid fa-folder'],
        ['route' => 'document-manager.categories.index', 'label' => 'Categorias', 'icon' => 'fa-solid fa-sitemap'],
        ['route' => 'document-manager.tags.index', 'label' => 'Tags', 'icon' => 'fa-solid fa-tags'],
        ['route' => 'document-manager.workflow.index', 'label' => 'Workflow', 'icon' => 'fa-solid fa-diagram-project'],
        ['route' => 'document-manager.ai.index', 'label' => 'AI', 'icon' => 'fa-solid fa-brain'],
        ['route' => 'document-manager.diagnostics.index', 'label' => 'Diagnostics', 'icon' => 'fa-solid fa-stethoscope'],
    ];
@endphp

<div class="dms-nav">
    @foreach($items as $item)
        @if(Route::has($item['route']))
            @php
                $activePattern = str_replace('.index', '.*', $item['route']);
                $isActive = request()->routeIs($item['route']) || request()->routeIs($activePattern);
            @endphp
            <a href="{{ route($item['route']) }}" class="dms-nav__item {{ $isActive ? 'is-active' : '' }}">
                <i class="{{ $item['icon'] }}"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        @endif
    @endforeach
</div>
