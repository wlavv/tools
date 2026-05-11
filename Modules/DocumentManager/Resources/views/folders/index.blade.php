@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    @php
        $foldersByParent = collect($folders)->groupBy(fn ($folder) => (int) ($folder->parent_id ?? 0));
    @endphp
    <div class="dms-card">
        <div class="dms-card__head">
            <div>
                <span class="dms-eyebrow">Arvore operacional</span>
                <h3>Folders</h3>
            </div>
            <span class="dms-badge dms-badge--soft">{{ collect($folders)->count() }} folders</span>
        </div>

        <div class="dms-category-tree">
            @forelse($foldersByParent->get(0, collect()) as $folder)
                @include('documentmanager::folders.tree-item', [
                    'folder' => $folder,
                    'childrenByParent' => $foldersByParent,
                    'level' => 0,
                ])
            @empty
                <div class="dms-empty">Sem folders.</div>
            @endforelse
        </div>
    </div>
@endsection
