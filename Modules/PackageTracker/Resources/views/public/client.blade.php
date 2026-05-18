<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ $theme['brand_name'] }} - Tracking Portal</title>
    <style>
        :root { --pt-primary: {{ $theme['primary_color'] }}; --pt-accent: {{ $theme['accent_color'] }}; --pt-bg: {{ $theme['background_color'] }}; --pt-text: #111827; --pt-muted: #64748b; --pt-border: #dbe3ea; --pt-surface: #fff; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; color: var(--pt-text); background: var(--pt-bg); font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .page { max-width: 1080px; margin: 0 auto; padding: 28px 18px 56px; }
        .brand { display: flex; align-items: center; gap: 14px; margin-bottom: 28px; }
        .brand img { width: auto; max-width: 150px; max-height: 48px; object-fit: contain; }
        .brand-mark { width: 44px; height: 44px; border-radius: 8px; background: var(--pt-primary); display: inline-flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; }
        .brand-name { font-size: 19px; font-weight: 750; }
        .panel { background: var(--pt-surface); border: 1px solid var(--pt-border); border-radius: 8px; padding: 24px; box-shadow: 0 16px 42px rgba(15, 23, 42, .08); }
        .hero { margin-bottom: 22px; }
        h1 { margin: 0 0 8px; font-size: clamp(30px, 5vw, 50px); line-height: 1.05; letter-spacing: 0; }
        .summary { color: var(--pt-muted); font-size: 17px; line-height: 1.55; }
        .shipments { display: grid; gap: 12px; }
        .shipment { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 16px; border: 1px solid var(--pt-border); border-radius: 8px; color: inherit; text-decoration: none; }
        .shipment:hover { border-color: var(--pt-accent); }
        .tracking { font-weight: 800; overflow-wrap: anywhere; }
        .meta { color: var(--pt-muted); margin-top: 4px; font-size: 14px; }
        .status { display: inline-flex; padding: 7px 11px; border-radius: 999px; background: color-mix(in srgb, var(--pt-primary) 12%, white); color: var(--pt-primary); font-weight: 800; font-size: 13px; white-space: nowrap; }
        .empty { color: var(--pt-muted); margin: 0; }
        @media (max-width: 680px) { .shipment { grid-template-columns: 1fr; } .status { justify-self: start; } }
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

        <section class="panel hero">
            <h1>{{ $client->name }}</h1>
            <div class="summary">Portal de acompanhamento das suas expedições.</div>
        </section>

        <section class="panel shipments">
            @forelse($shipments as $shipment)
                <a class="shipment" href="{{ $shipment->publicUrl() }}">
                    <span>
                        <span class="tracking">{{ $shipment->tracking_number }}</span>
                        <span class="meta">{{ $shipment->carrier?->name ?: '-' }} · {{ $shipment->order_reference ?: 'Sem referencia' }}</span>
                    </span>
                    <span class="status">{{ config('package_tracker.normalized_statuses.' . $shipment->status, $shipment->statusEnum()->label()) }}</span>
                </a>
            @empty
                <p class="empty">Ainda não existem expedições publicadas para esta conta.</p>
            @endforelse

            {{ $shipments->links() }}
        </section>
    </main>
</body>
</html>
