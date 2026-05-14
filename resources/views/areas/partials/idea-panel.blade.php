@php
    $ideaPanel = config('area_ideas.' . $areaKey);
@endphp

@if($ideaPanel)
    @once
        @push('styles')
            <style>
                .area-ideas-panel { display: grid; gap: 14px; }
                .area-ideas-header { display: flex; align-items: flex-start; gap: 14px; border: 1px solid rgba(148, 163, 184, .22); background: var(--card-bg, #fff); padding: 16px; }
                .area-ideas-header__icon { width: 44px; height: 44px; display: inline-grid; place-items: center; flex: 0 0 auto; border: 1px solid rgba(37, 99, 235, .22); background: rgba(37, 99, 235, .12); color: #60a5fa; font-size: 18px; }
                .area-ideas-header h2 { margin: 0; font-size: 1.15rem; font-weight: 850; }
                .area-ideas-header p { margin: 4px 0 0; color: var(--text-muted, #64748b); font-weight: 650; line-height: 1.45; }
                .area-ideas-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px; }
                .area-ideas-card { border: 1px solid rgba(148, 163, 184, .22); background: var(--card-bg, #fff); padding: 14px; }
                .area-ideas-card h3 { margin: 0 0 10px; font-size: .98rem; font-weight: 850; }
                .area-ideas-list { display: grid; gap: 8px; margin: 0; padding: 0; list-style: none; }
                .area-ideas-list li { display: grid; grid-template-columns: 18px minmax(0, 1fr); gap: 8px; align-items: start; color: var(--text-muted, #64748b); font-size: .9rem; font-weight: 650; line-height: 1.4; }
                .area-ideas-list i { color: #22c55e; font-size: 12px; margin-top: 4px; }
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
