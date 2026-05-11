@php
    $children = $childrenByParent->get((int) $category->id, collect());
    $hasChildren = $children->isNotEmpty();
@endphp

<details class="dms-tree-node" style="--tree-level: {{ (int) $level }}" @if((int) $level === 0) open @endif>
    <summary class="dms-tree-row">
        <span class="dms-tree-row__toggle">
            <i class="fa-solid {{ $hasChildren ? 'fa-chevron-right' : 'fa-minus' }}"></i>
        </span>
        <span class="dms-tree-row__icon" style="color: {{ $category->color ?: '#d4a017' }}">
            <i class="{{ $category->icon ?: 'fa-solid fa-folder' }}"></i>
        </span>
        <span class="dms-tree-row__main">
            <strong>{{ $category->name }}</strong>
            <small>{{ $category->slug ?: 'sem slug' }}</small>
        </span>
        <span class="dms-tree-row__meta">{{ $category->workspace_name ?: 'Global' }}</span>
        <a href="{{ route('document-manager.categories.edit', $category->id) }}" class="btn btn-outline-warning btn-sm" onclick="event.stopPropagation();">
            <i class="fa-solid fa-pencil"></i>
        </a>
    </summary>

    @if($hasChildren)
        <div class="dms-tree-children">
            @foreach($children as $child)
                @include('documentmanager::categories.tree-item', [
                    'category' => $child,
                    'childrenByParent' => $childrenByParent,
                    'level' => $level + 1,
                ])
            @endforeach
        </div>
    @endif
</details>
