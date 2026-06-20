@extends('layouts.app')

@section('content')
    <style>
        .infra-docs-layout{display:grid;grid-template-columns:minmax(180px,2fr) minmax(0,10fr);gap:14px;align-items:start}
        .infra-docs-nav,.infra-docs-article{border:1px solid var(--border-soft,rgba(148,163,184,.22));background:var(--bg-panel,var(--card-bg,#fff));color:var(--text-primary,#111827);min-width:0}
        .infra-docs-nav{position:sticky;top:96px;max-height:calc(100vh - 118px);overflow:auto;padding:10px}
        .infra-docs-search{position:sticky;top:0;z-index:2;background:var(--bg-panel,var(--card-bg,#fff));padding-bottom:10px;margin-bottom:8px;border-bottom:1px solid var(--border-soft,rgba(148,163,184,.16))}
        .infra-docs-search label{display:block;color:#d4a017;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.06em;margin-bottom:6px}
        .infra-docs-search-input{width:100%;min-height:36px;border:1px solid var(--border-soft,rgba(148,163,184,.25));background:var(--bg-panel-soft,rgba(148,163,184,.06));color:var(--text-primary,#111827);padding:7px 9px;font-size:.82rem;font-weight:800}
        .infra-docs-search-input:focus{outline:none;border-color:rgba(212,160,23,.55);box-shadow:0 0 0 2px rgba(212,160,23,.12)}
        .infra-docs-search-empty{display:none;padding:8px;color:var(--text-muted,#64748b);font-size:.8rem;font-weight:800}
        .infra-docs-nav-group{display:grid;gap:5px;margin-bottom:12px}
        .infra-docs-nav-title{display:flex;align-items:center;gap:7px;padding:6px 7px;color:#d4a017;font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.06em}
        .infra-docs-nav-link{display:grid;grid-template-columns:18px minmax(0,1fr);gap:7px;align-items:center;padding:7px;border:1px solid transparent;color:var(--text-muted,#64748b);text-decoration:none;font-size:.78rem;font-weight:800}
        .infra-docs-nav-link:hover,.infra-docs-nav-link.is-active{border-color:rgba(212,160,23,.32);background:rgba(212,160,23,.10);color:var(--text-primary,#111827);text-decoration:none}
        .infra-docs-nav-link span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .infra-docs-nav-link i{color:inherit;text-align:center}
        .infra-docs-article{padding:22px}
        .infra-docs-empty{padding:18px;color:var(--text-muted,#64748b)}
        .infra-docs-article :is(h1,h2,h3){font-weight:900;margin-top:1.2rem;margin-bottom:.65rem}
        .infra-docs-article h1{font-size:1.55rem;margin-top:0}
        .infra-docs-article h2{font-size:1.2rem}
        .infra-docs-article h3{font-size:1rem}
        .infra-docs-article p,.infra-docs-article li{line-height:1.65}
        .infra-docs-article pre{background:#0f172a;color:#dbeafe;padding:12px;overflow:auto}
        .infra-docs-article code{color:#dbeafe}
        .infra-docs-article :not(pre)>code{background:rgba(148,163,184,.16);color:var(--text-primary,#111827);padding:2px 5px}
        .infra-docs-article table{width:100%;border-collapse:collapse;margin:12px 0}
        .infra-docs-article th,.infra-docs-article td{border:1px solid var(--border-soft,rgba(148,163,184,.25));padding:8px;text-align:left;vertical-align:top}
        @media(max-width:1000px){.infra-docs-layout{grid-template-columns:1fr}.infra-docs-nav{position:static;max-height:none}.infra-docs-nav-group{margin-bottom:8px}.infra-docs-nav-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:6px}}
    </style>

    @php
        $activeSlug = $selectedDocument['slug'] ?? null;
    @endphp

    <div class="infra-docs-layout">
        <aside class="infra-docs-nav">
            <div class="infra-docs-search">
                <label for="infraDocsSearch">Pesquisar</label>
                <input id="infraDocsSearch" class="infra-docs-search-input" type="search" placeholder="Modulo, API, backup..." autocomplete="off">
                <div class="infra-docs-search-empty" id="infraDocsSearchEmpty">Sem resultados.</div>
            </div>

            @forelse($documents as $group => $items)
                <div class="infra-docs-nav-group">
                    <div class="infra-docs-nav-title">
                        <i class="fa-solid {{ $group === 'Modulos' ? 'fa-cubes' : 'fa-book' }}"></i>
                        <span>{{ $group }}</span>
                    </div>

                    <div class="infra-docs-nav-links">
                        @foreach($items as $document)
                            <a
                                href="{{ route('admin.infrastructure.documentation.show', $document['slug']) }}"
                                class="infra-docs-nav-link {{ $activeSlug === $document['slug'] ? 'is-active' : '' }}"
                                title="{{ $document['title'] }}"
                                data-doc-search="{{ Str::lower($document['title'] . ' ' . $document['summary'] . ' ' . $document['relative_path'] . ' ' . $group) }}"
                            >
                                <i class="fa-solid {{ $document['source'] === 'module' ? 'fa-cube' : 'fa-file-lines' }}"></i>
                                <span>{{ $document['title'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="infra-docs-empty">Sem documentacao.</div>
            @endforelse
        </aside>

        <article class="infra-docs-article">
            @if($selectedDocument)
                {!! $selectedDocument['html'] !!}
            @else
                <div class="infra-docs-empty">Sem documentacao encontrada.</div>
            @endif
        </article>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const search = document.getElementById('infraDocsSearch');
            const empty = document.getElementById('infraDocsSearchEmpty');
            const links = Array.from(document.querySelectorAll('[data-doc-search]'));
            const groups = Array.from(document.querySelectorAll('.infra-docs-nav-group'));

            if (!search) {
                return;
            }

            search.addEventListener('input', function () {
                const query = search.value.trim().toLowerCase();
                let visible = 0;

                links.forEach(function (link) {
                    const matches = !query || link.dataset.docSearch.includes(query);
                    link.hidden = !matches;
                    if (matches) visible++;
                });

                groups.forEach(function (group) {
                    const hasVisibleLinks = Array.from(group.querySelectorAll('[data-doc-search]'))
                        .some(function (link) { return !link.hidden; });
                    group.hidden = !hasVisibleLinks;
                });

                if (empty) {
                    empty.style.display = visible === 0 ? 'block' : 'none';
                }
            });
        });
    </script>
@endpush
