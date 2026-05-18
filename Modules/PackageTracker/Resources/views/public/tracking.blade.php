<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $theme['brand_name'] }} - Tracking</title>
    <style>
        :root {
            --pt-primary: {{ $theme['primary_color'] }};
            --pt-accent: {{ $theme['accent_color'] }};
            --pt-bg: {{ $theme['background_color'] }};
            --pt-text: #111827;
            --pt-muted: #64748b;
            --pt-border: #dbe3ea;
            --pt-surface: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            color: var(--pt-text);
            background: var(--pt-bg);
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        .page { max-width: 1040px; margin: 0 auto; padding: 28px 18px 56px; }
        .brand { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
        .brand img { width: auto; max-width: 150px; max-height: 48px; object-fit: contain; }
        .brand-mark {
            width: 44px; height: 44px; border-radius: 8px; background: var(--pt-primary);
            display: inline-flex; align-items: center; justify-content: center; color: #fff; font-weight: 800;
        }
        .brand-name { font-size: 19px; font-weight: 750; }
        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(260px, .8fr);
            gap: 22px;
            align-items: stretch;
            margin-bottom: 22px;
        }
        .panel {
            background: var(--pt-surface);
            border: 1px solid var(--pt-border);
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 16px 42px rgba(15, 23, 42, .08);
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 11px;
            border-radius: 999px;
            background: color-mix(in srgb, var(--pt-primary) 12%, white);
            color: var(--pt-primary);
            font-weight: 700;
            font-size: 13px;
        }
        .status-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
        h1 { margin: 18px 0 8px; font-size: clamp(30px, 5vw, 54px); line-height: 1.02; letter-spacing: 0; }
        .summary { color: var(--pt-muted); font-size: 17px; line-height: 1.55; max-width: 680px; }
        .facts { display: grid; gap: 14px; }
        .fact { border-bottom: 1px solid var(--pt-border); padding-bottom: 13px; }
        .fact:last-child { border-bottom: 0; padding-bottom: 0; }
        .label { color: var(--pt-muted); font-size: 12px; text-transform: uppercase; letter-spacing: .04em; font-weight: 800; }
        .value { margin-top: 4px; font-size: 17px; font-weight: 700; overflow-wrap: anywhere; }
        .timeline { margin-top: 22px; }
        .timeline h2 { margin: 0 0 18px; font-size: 22px; }
        .event { display: grid; grid-template-columns: 32px minmax(0, 1fr); gap: 14px; }
        .event-line { position: relative; display: flex; justify-content: center; }
        .event-line::after { content: ""; width: 2px; background: var(--pt-border); position: absolute; top: 24px; bottom: -10px; }
        .event:last-child .event-line::after { display: none; }
        .event-dot { width: 14px; height: 14px; border: 3px solid var(--pt-accent); background: #fff; border-radius: 50%; margin-top: 4px; z-index: 1; }
        .event-body { padding-bottom: 24px; }
        .event-title { font-weight: 800; }
        .event-meta { color: var(--pt-muted); font-size: 14px; margin-top: 4px; }
        .event-description { margin-top: 6px; color: #334155; line-height: 1.45; }
        .empty { color: var(--pt-muted); margin: 0; }
        @media (max-width: 760px) {
            .page { padding-top: 20px; }
            .hero { grid-template-columns: 1fr; }
            .panel { padding: 20px; }
            h1 { font-size: 36px; }
        }
    </style>
</head>
<body>
    <main class="page">
        <header class="brand">
            @if($theme['logo_url'])
                <img src="{{ $theme['logo_url'] }}" alt="{{ $theme['brand_name'] }}">
            @else
                <span class="brand-mark">{{ mb_strtoupper(mb_substr($theme['brand_name'], 0, 1)) }}</span>
            @endif
            <div class="brand-name">{{ $theme['brand_name'] }}</div>
        </header>

        <section class="hero">
            <div class="panel">
                <span class="status-pill"><span class="status-dot"></span>{{ $statusLabel }}</span>
                <h1>{{ $shipment->isTerminal() ? 'Entrega atualizada' : 'A sua encomenda está em movimento' }}</h1>
                <div class="summary">
                    @if($shipment->last_location)
                        Último registo em {{ $shipment->last_location }}.
                    @else
                        Acompanhe aqui a evolução da expedição assim que a transportadora publicar novos eventos.
                    @endif
                </div>
            </div>

            <aside class="panel facts" aria-label="Detalhes da expedição">
                <div class="fact">
                    <div class="label">Transportadora</div>
                    <div class="value">{{ $shipment->carrier?->name ?: '-' }}</div>
                </div>
                <div class="fact">
                    <div class="label">Tracking</div>
                    <div class="value">{{ $trackingNumber }}</div>
                </div>
                <div class="fact">
                    <div class="label">Encomenda</div>
                    <div class="value">{{ $shipment->order_reference ?: '-' }}</div>
                </div>
                <div class="fact">
                    <div class="label">Previsão</div>
                    <div class="value">{{ optional($shipment->estimated_delivery_at)->format('d/m/Y H:i') ?: '-' }}</div>
                </div>
            </aside>
        </section>

        <section class="panel timeline">
            <h2>Timeline</h2>
            @forelse($shipment->events as $event)
                <article class="event">
                    <div class="event-line"><span class="event-dot"></span></div>
                    <div class="event-body">
                        <div class="event-title">{{ config('package_tracker.normalized_statuses.' . $event->normalized_status, ucfirst(str_replace('_', ' ', $event->normalized_status))) }}</div>
                        <div class="event-meta">
                            {{ optional($event->event_at)->format('d/m/Y H:i') ?: '-' }}
                            @if($event->location)
                                · {{ $event->location }}
                            @endif
                        </div>
                        @if($event->description)
                            <div class="event-description">{{ $event->description }}</div>
                        @endif
                    </div>
                </article>
            @empty
                <p class="empty">Ainda não existem eventos de tracking para esta expedição.</p>
            @endforelse
        </section>
    </main>
</body>
</html>
