@extends('layouts.app')

@section('content')
    <style>
        .settings-focus-shell{display:grid;gap:12px}
        .settings-focus-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px}
        .settings-focus-card{position:relative;overflow:hidden;display:flex;gap:14px;align-items:flex-start;min-height:126px;padding:16px;border:1px solid rgba(148,163,184,.25);background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(248,250,252,.86));box-shadow:0 8px 24px rgba(15,23,42,.08);color:#0f172a;text-decoration:none}
        .settings-focus-card:hover{border-color:color-mix(in srgb,var(--focus-color,#2563eb) 42%,rgba(148,163,184,.25));color:#0f172a;text-decoration:none}
        .settings-focus-card__icon{width:46px;height:46px;flex:0 0 46px;display:flex;align-items:center;justify-content:center;border:1px solid color-mix(in srgb,var(--focus-color,#2563eb) 28%,transparent);background:color-mix(in srgb,var(--focus-color,#2563eb) 16%,transparent);color:var(--focus-color,#2563eb);font-size:20px}
        .settings-focus-card__body{min-width:0;display:grid;gap:8px}
        .settings-focus-card__top{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}
        .settings-focus-card__title{font-weight:900;font-size:14px;line-height:1.2}
        .settings-focus-card__count{font-size:28px;line-height:1;font-weight:900;color:var(--focus-color,#2563eb)}
        .settings-focus-card__description{font-size:12px;color:#64748b;font-weight:700;line-height:1.35}
        .settings-focus-card__cta{font-size:12px;font-weight:900;color:var(--focus-color,#2563eb);display:inline-flex;align-items:center;gap:6px}
        .settings-focus-card.ok{--focus-color:#16a34a}
        .settings-focus-card.info{--focus-color:#2563eb}
        .settings-focus-card.warning{--focus-color:#d97706}
        .settings-focus-card.critical{--focus-color:#dc2626}
        body.theme-dark .settings-focus-card,
        body[data-theme="dark"] .settings-focus-card{background:linear-gradient(135deg,rgba(15,23,42,.96),rgba(30,41,59,.86));border-color:rgba(148,163,184,.22);color:#f8fafc}
        body.theme-dark .settings-focus-card:hover,
        body[data-theme="dark"] .settings-focus-card:hover{color:#f8fafc}
        body.theme-dark .settings-focus-card__description,
        body[data-theme="dark"] .settings-focus-card__description{color:#94a3b8}
    </style>

    @if(!empty($focusCards))
        <section class="settings-focus-shell" aria-label="Settings focus">
            <div class="settings-focus-grid">
                @foreach($focusCards as $card)
                    <a class="settings-focus-card {{ $card['severity'] }}" href="{{ $card['url'] }}">
                        <div class="settings-focus-card__icon"><i class="{{ $card['icon'] }}" aria-hidden="true"></i></div>
                        <div class="settings-focus-card__body">
                            <div class="settings-focus-card__top">
                                <div class="settings-focus-card__title">{{ $card['title'] }}</div>
                                <div class="settings-focus-card__count">{{ $card['count'] }}</div>
                            </div>
                            <div class="settings-focus-card__description">{{ $card['description'] }}</div>
                            <div class="settings-focus-card__cta">
                                <span>{{ $card['count'] > 0 ? 'Ver problemas' : 'Sem problemas' }}</span>
                                <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
