@php
    $children = $childrenByParent->get((int) $folder->id, collect());
    $hasChildren = $children->isNotEmpty();
@endphp

<details class="dms-tree-node" style="--tree-level: {{ (int) $level }}">
    <summary class="dms-tree-row">
        <span class="dms-tree-row__toggle">
            <i class="fa-solid {{ $hasChildren ? 'fa-chevron-right' : 'fa-minus' }}"></i>
        </span>
        <span class="dms-tree-row__icon">
            <i class="fa-solid fa-folder"></i>
        </span>
        <span class="dms-tree-row__main">
            <strong>{{ $folder->name }}</strong>
            <small>{{ $folder->path ?: ($folder->slug ?: 'raiz') }}</small>
        </span>
        <span class="dms-tree-row__meta">{{ $folder->workspace_name ?: 'Global' }}</span>
        <a href="{{ route('document-manager.folders.edit', $folder->id) }}" class="btn btn-outline-warning btn-sm" onclick="event.stopPropagation();">
            <i class="fa-solid fa-pencil"></i>
        </a>
    </summary>

    @if($hasChildren)
        <div class="dms-tree-children">
            @foreach($children as $child)
                @include('documentmanager::folders.tree-item', [
                    'folder' => $child,
                    'childrenByParent' => $childrenByParent,
                    'level' => $level + 1,
                ])
            @endforeach
        </div>
    @endif
</details>
