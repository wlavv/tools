@php
    $ideaPanel = config('area_ideas.' . $areaKey);
@endphp

@if($ideaPanel)
    @once
        @push('styles')
            <style>
                .app-shell .area-ideas-panel,
                .area-ideas-panel { display: grid; gap: 14px; background: transparent !important; border: 0 !important; box-shadow: none !important; }
                .area-ideas-header { display: flex; align-items: flex-start; gap: 14px; border: 1px solid var(--border-soft, rgba(180, 194, 214, .16)); background: var(--bg-card-2, var(--card-bg, var(--bg-card, #2f3a48))); padding: 16px; color: var(--text-primary, #f0f4f9); }
                .area-ideas-header__icon { width: 44px; height: 44px; display: inline-grid; place-items: center; flex: 0 0 auto; border: 1px solid var(--lsg-bo-btn-primary-border, rgba(96, 165, 250, .76)); background: var(--lsg-bo-btn-primary-bg, #1d4ed8); color: var(--lsg-bo-btn-primary-text, #eff6ff); font-size: 18px; }
                .area-ideas-header h2 { margin: 0; font-size: 1.15rem; font-weight: 850; }
                .area-ideas-header p { margin: 4px 0 0; color: var(--text-muted, #9aa7b8); font-weight: 650; line-height: 1.45; }
                .area-ideas-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px; }
                .area-ideas-card { border: 1px solid var(--border-soft, rgba(180, 194, 214, .16)); background: var(--bg-card-2, var(--card-bg, var(--bg-card, #2f3a48))); color: var(--text-primary, #f0f4f9); padding: 14px; }
                .area-ideas-card h3 { margin: 0 0 10px; font-size: .98rem; font-weight: 850; }
                .area-ideas-list { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
                .area-ideas-list li { display: grid; grid-template-columns: 18px minmax(0, 1fr); gap: 8px; align-items: start; color: var(--text-muted, #9aa7b8); font-size: .9rem; font-weight: 650; line-height: 1.4; }
                .area-ideas-list i { color: var(--success, #4bb782); font-size: 12px; margin-top: 4px; }
            </style>
        @endpush
    @endonce

    <section class="area-ideas-panel">
        <header class="area-ideas-header">
            <span class="area-ideas-header__icon"><i class="{{ $ideaPanel['icon'] }}"></i></span>
            <div>
                <h2>{{ $ideaPanel['title'] }}</h2>
                <p>{{ $ideaPanel['subtitle'] }}</p>
            </div>
        </header>

        <div class="area-ideas-grid">
            @foreach($ideaPanel['topics'] as $topic)
                <article class="area-ideas-card">
                    <h3>{{ $topic['title'] }}</h3>
                    <ul class="area-ideas-list">
                        @foreach($topic['items'] as $item)
                            <li><i class="fa-solid fa-circle-check"></i><span>{{ $item }}</span></li>
                        @endforeach
                    </ul>
                </article>
            @endforeach
        </div>
    </section>
@endif
