@php
    $panel = $productGrowthPanel ?? app(\App\Services\ProductGrowthAreaPanelService::class)->forArea($areaKey ?? 'support');
@endphp

@if($panel['enabled'] ?? false)

@once
    @push('styles')
        <style>
            .pg-area-panel{display:grid;gap:14px;margin-bottom:14px}
            .pg-area-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;border:1px solid var(--border-soft,rgba(180,194,214,.16));background:var(--bg-card-2,var(--card-bg,var(--bg-card,#2f3a48)));padding:16px;color:var(--text-primary,#f0f4f9)}
            .pg-area-head__main{display:flex;gap:14px;align-items:flex-start;min-width:0}
            .pg-area-head__icon{width:44px;height:44px;display:grid;place-items:center;flex:0 0 44px;border:1px solid rgba(96,165,250,.35);background:rgba(96,165,250,.12);color:#60a5fa;font-size:18px}
            .pg-area-head h2{margin:0;font-size:1.08rem;font-weight:900}
            .pg-area-head p{margin:4px 0 0;color:var(--text-muted,#9aa7b8);font-size:.9rem;font-weight:650;line-height:1.45}
            .pg-area-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;align-items:start}
            .pg-area-card{min-width:0;border:1px solid var(--border-soft,rgba(180,194,214,.16));background:var(--bg-card-2,var(--card-bg,var(--bg-card,#2f3a48)));color:var(--text-primary,#f0f4f9);padding:0;display:grid;gap:0}
            .pg-area-counter{width:100%;display:grid;grid-template-columns:34px minmax(0,1fr) 24px;align-items:center;gap:12px;border:0;background:rgba(96,165,250,.08);padding:12px;text-align:left;color:inherit;cursor:pointer}
            .pg-area-counter small{display:block;color:var(--text-muted,#9aa7b8);text-transform:uppercase;font-size:.68rem;font-weight:900;letter-spacing:.05em}
            .pg-area-counter strong{display:block;margin-top:4px;font-size:1.35rem;line-height:1}
            .pg-area-counter i{width:34px;height:34px;display:grid;place-items:center;border:1px solid rgba(96,165,250,.25);background:rgba(96,165,250,.12);color:#60a5fa}
            .pg-area-counter .pg-area-chevron{width:24px;height:24px;border:0;background:transparent;color:var(--text-muted,#9aa7b8);transition:transform .18s ease}
            .pg-area-card.is-open .pg-area-chevron{transform:rotate(180deg)}
            .pg-area-counter--warning{border-color:rgba(245,158,11,.25);background:rgba(245,158,11,.09)}
            .pg-area-counter--warning i{border-color:rgba(245,158,11,.28);background:rgba(245,158,11,.14);color:#f59e0b}
            .pg-area-counter--success{border-color:rgba(34,197,94,.25);background:rgba(34,197,94,.09)}
            .pg-area-counter--success i{border-color:rgba(34,197,94,.28);background:rgba(34,197,94,.14);color:#22c55e}
            .pg-area-body{display:none;border-top:1px solid var(--border-soft,rgba(180,194,214,.16));padding:10px}
            .pg-area-card.is-open .pg-area-body{display:block}
            .pg-area-list{display:grid;gap:8px;margin:0;padding:0;list-style:none}
            .pg-area-list a{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:10px;align-items:center;border:1px solid var(--border-soft,rgba(180,194,214,.16));background:rgba(148,163,184,.06);padding:10px;color:var(--text-primary,#f0f4f9);text-decoration:none}
            .pg-area-list a:hover{border-color:rgba(212,160,23,.45);background:rgba(212,160,23,.10)}
            .pg-area-list strong{display:block;font-size:.86rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
            .pg-area-list span{display:block;margin-top:2px;color:var(--text-muted,#9aa7b8);font-size:.75rem;font-weight:700}
            .pg-area-empty{color:var(--text-muted,#9aa7b8);font-size:.86rem;font-weight:700;border:1px dashed var(--border-soft,rgba(180,194,214,.16));padding:12px}
            @media(max-width:1100px){.pg-area-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
            @media(max-width:800px){.pg-area-grid{grid-template-columns:1fr}.pg-area-head{display:grid}.pg-area-head .lsg-action-btn{justify-self:start}}
        </style>
    @endpush
    @push('scripts')
        <script>
            document.addEventListener('click', function (event) {
                const trigger = event.target.closest('[data-pg-area-toggle]');
                if (!trigger) {
                    return;
                }

                const card = trigger.closest('.pg-area-card');
                if (!card) {
                    return;
                }

                card.classList.toggle('is-open');
                trigger.setAttribute('aria-expanded', card.classList.contains('is-open') ? 'true' : 'false');
            });
        </script>
    @endpush
@endonce

<section class="pg-area-panel">
    <header class="pg-area-head">
        <div class="pg-area-head__main">
            <span class="pg-area-head__icon"><i class="{{ $panel['icon'] }}"></i></span>
            <div>
                <h2>{{ $panel['title'] }}</h2>
                <p>{{ $panel['subtitle'] }}</p>
            </div>
        </div>
        @if($panel['entry_url'])
            <a href="{{ $panel['entry_url'] }}" class="lsg-action-btn lsg-action-btn--primary lsg-action-btn--compact">
                <i class="fa-solid fa-arrow-right"></i> Abrir area
            </a>
        @endif
    </header>

    <div class="pg-area-grid">
        @foreach(($panel['queues'] ?? []) as $queueKey => $queue)
            <article class="pg-area-card">
                <button type="button" class="pg-area-counter {{ $queue['class'] }}" data-pg-area-toggle aria-expanded="false">
                    <i class="{{ $queue['icon'] }}"></i>
                    <div>
                        <small>{{ $queue['label'] }}</small>
                        <strong>{{ $panel[$queueKey]->count() }}</strong>
                    </div>
                    <i class="fa-solid fa-chevron-down pg-area-chevron"></i>
                </button>

                <div class="pg-area-body">
                    @if($panel[$queueKey]->isNotEmpty())
                        <ul class="pg-area-list">
                            @foreach($panel[$queueKey] as $product)
                                <li>
                                    <a href="{{ $panel['entry_url'] ? $panel['entry_url'] . '/products/' . $product->id : '#' }}">
                                        <span>
                                            <strong>{{ $product->name }}</strong>
                                            <span>{{ $product->internal_sku }} · {{ $product->brand?->name ?? 'Sem marca' }}</span>
                                        </span>
                                        <i class="fa-solid fa-chevron-right"></i>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="pg-area-empty">Sem produtos nesta fila.</div>
                    @endif
                </div>
            </article>
        @endforeach
    </div>
</section>
@endif
