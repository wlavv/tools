@extends('config-inspector::layouts.module')

@section('content')
@php
    $activeResult = $results[$active] ?? reset($results);
    $severityLabels = [
        'critical' => 'Crítico',
        'error' => 'Erro',
        'warning' => 'Aviso',
        'info' => 'Info',
        'success' => 'OK',
    ];
@endphp

<style>
    .ci-shell { --ci-radius: 0; --ci-border: rgba(148,163,184,.24); --ci-bg: rgba(255,255,255,.78); --ci-muted: #64748b; }
    .ci-shell .card,
    .ci-shell .btn,
    .ci-shell .badge,
    .ci-shell .alert,
    .ci-shell .dropdown-menu,
    .ci-shell .form-control,
    .ci-shell .form-select,
    .ci-shell .list-group,
    .ci-shell .list-group-item,
    .ci-shell pre,
    .ci-shell details,
    .ci-shell summary { border-radius: 0 !important; }
    .ci-dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 12px; }
    .ci-dashboard-metric { position: relative; overflow: hidden; border-radius: 0; padding: 16px; min-height: 104px; border: 1px solid rgba(148,163,184,.25); background: linear-gradient(135deg, rgba(255,255,255,.96), rgba(248,250,252,.86)); box-shadow: 0 8px 24px rgba(15,23,42,.08); display: flex; justify-content: space-between; gap: 14px; align-items: center; }
    .ci-dashboard-metric__label { font-size: 12px; text-transform: uppercase; color: #64748b; font-weight: 800; letter-spacing: .04em; }
    .ci-dashboard-metric__value { font-size: 30px; line-height: 1; font-weight: 900; color: #0f172a; margin-top: 6px; }
    .ci-dashboard-metric__icon { width: 46px; height: 46px; border-radius: 0; display: flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--metric-color, #2563eb) 16%, transparent); color: var(--metric-color, #2563eb); font-size: 20px; border: 1px solid color-mix(in srgb, var(--metric-color, #2563eb) 28%, transparent); flex: 0 0 46px; }
    .ci-dashboard-metric.critical { --metric-color: #7f1d1d; }
    .ci-dashboard-metric.error { --metric-color: #dc2626; }
    .ci-dashboard-metric.warning { --metric-color: #f59e0b; }
    .ci-dashboard-metric.info { --metric-color: #2563eb; }
    .ci-dashboard-metric.success { --metric-color: #16a34a; }
    .ci-dashboard-metric.health { --metric-color: #0f172a; }
    .ci-grid { display: grid; grid-template-columns: minmax(230px, 2.5fr) minmax(0, 9.5fr); gap: 16px; }
    .ci-sidebar, .ci-panel { border: 1px solid var(--ci-border); border-radius: var(--ci-radius); background: var(--ci-bg); box-shadow: 0 10px 24px rgba(15,23,42,.05); overflow: hidden; }
    .ci-tabs { list-style: none; padding: 8px; margin: 0; display: flex; flex-direction: column; gap: 6px; }
    .ci-tab { display: block; border: 1px solid transparent; border-radius: var(--ci-radius); padding: 10px; text-decoration: none; color: #334155; transition: all .15s ease; }
    .ci-tab:hover { background: rgba(15,23,42,.04); color: #0f172a; text-decoration: none; }
    .ci-tab.is-active { background: linear-gradient(135deg, rgba(15,23,42,.95), rgba(30,41,59,.92)); color: #fff; box-shadow: 0 8px 18px rgba(15,23,42,.18); }
    .ci-tab-main { display: flex; align-items: center; gap: 8px; font-weight: 700; }
    .ci-tab-desc { display: block; margin-top: 4px; font-size: .78rem; opacity: .76; line-height: 1.25; }
    .ci-mini-badges { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 8px; }
    .ci-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 22px; height: 20px; border-radius: var(--ci-radius); padding: 0 6px; font-size: .72rem; font-weight: 800; }
    .ci-badge--critical { background: #7f1d1d; color: #fff; }
    .ci-badge--error { background: #dc2626; color: #fff; }
    .ci-badge--warning { background: #f59e0b; color: #111827; }
    .ci-badge--info { background: #2563eb; color: #fff; }
    .ci-badge--success { background: #16a34a; color: #fff; }
    .ci-panel-head { padding: 16px; border-bottom: 1px solid var(--ci-border); display: flex; justify-content: space-between; gap: 12px; align-items: center; }
    .ci-panel-title { margin: 0; font-weight: 800; color: #0f172a; }
    .ci-panel-desc { color: var(--ci-muted); font-size: .9rem; margin-top: 3px; }
    .ci-items { padding: 12px; display: grid; gap: 8px; }
    .ci-item { border: 1px solid var(--ci-border); border-left-width: 5px; border-radius: var(--ci-radius); background: rgba(255,255,255,.86); padding: 12px; }
    .ci-item--critical { border-left-color: #7f1d1d; }
    .ci-item--error { border-left-color: #dc2626; }
    .ci-item--warning { border-left-color: #f59e0b; }
    .ci-item--info { border-left-color: #2563eb; }
    .ci-item--success { border-left-color: #16a34a; }
    .ci-item-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
    .ci-item-title { font-weight: 800; color: #0f172a; }
    .ci-item-message { color: #475569; margin-top: 3px; }
    .ci-suggestion { margin-top: 8px; padding: 8px 10px; border-radius: var(--ci-radius); background: rgba(245,158,11,.12); color: #78350f; font-size: .88rem; }
    .ci-meta { margin-top: 8px; font-size: .8rem; color: #64748b; word-break: break-word; }
    .ci-summary-row { display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
    .ci-module-groups { padding: 12px; display: grid; gap: 8px; }
    .ci-module-group { border: 1px solid var(--ci-border); background: rgba(255,255,255,.86); }
    .ci-module-group[open] { box-shadow: 0 8px 20px rgba(15,23,42,.06); }
    .ci-module-summary { cursor: pointer; list-style: none; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px; }
    .ci-module-summary::-webkit-details-marker { display: none; }
    .ci-module-summary-main { display: flex; align-items: center; gap: 10px; min-width: 0; }
    .ci-module-summary-icon { width: 34px; height: 34px; border: 1px solid var(--ci-border); display: inline-flex; align-items: center; justify-content: center; color: #334155; background: rgba(248,250,252,.9); flex: 0 0 34px; }
    .ci-module-summary-title { font-weight: 900; color: #0f172a; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ci-module-summary-meta { color: var(--ci-muted); font-size: .82rem; margin-top: 2px; }
    .ci-module-summary-badges { display: flex; gap: 5px; flex-wrap: wrap; justify-content: flex-end; }
    .ci-module-items { border-top: 1px solid var(--ci-border); padding: 8px; display: grid; gap: 8px; background: rgba(248,250,252,.42); }
    .ci-module-items .ci-item { background: rgba(255,255,255,.92); }
    @media (max-width: 991px) { .ci-grid { grid-template-columns: 1fr; } .ci-tabs { flex-direction: row; overflow-x: auto; } .ci-tab { min-width: 220px; } }
</style>

<div class="ci-shell">
    <div class="ci-dashboard-grid">
        <div class="ci-dashboard-metric critical">
            <div><div class="ci-dashboard-metric__label">Critico</div><div class="ci-dashboard-metric__value">{{ $global['critical'] ?? 0 }}</div></div>
            <div class="ci-dashboard-metric__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
        </div>
        <div class="ci-dashboard-metric error">
            <div><div class="ci-dashboard-metric__label">Erros</div><div class="ci-dashboard-metric__value">{{ $global['error'] ?? 0 }}</div></div>
            <div class="ci-dashboard-metric__icon"><i class="fa-solid fa-circle-xmark"></i></div>
        </div>
        <div class="ci-dashboard-metric warning">
            <div><div class="ci-dashboard-metric__label">Avisos</div><div class="ci-dashboard-metric__value">{{ $global['warning'] ?? 0 }}</div></div>
            <div class="ci-dashboard-metric__icon"><i class="fa-solid fa-circle-exclamation"></i></div>
        </div>
        <div class="ci-dashboard-metric info">
            <div><div class="ci-dashboard-metric__label">Info</div><div class="ci-dashboard-metric__value">{{ $global['info'] ?? 0 }}</div></div>
            <div class="ci-dashboard-metric__icon"><i class="fa-solid fa-circle-info"></i></div>
        </div>
        <div class="ci-dashboard-metric success">
            <div><div class="ci-dashboard-metric__label">OK</div><div class="ci-dashboard-metric__value">{{ $global['success'] ?? 0 }}</div></div>
            <div class="ci-dashboard-metric__icon"><i class="fa-solid fa-circle-check"></i></div>
        </div>
        <div class="ci-dashboard-metric health">
            <div><div class="ci-dashboard-metric__label">Health</div><div class="ci-dashboard-metric__value">{{ $global['score'] ?? 0 }}%</div></div>
            <div class="ci-dashboard-metric__icon"><i class="fa-solid fa-heart-pulse"></i></div>
        </div>
    </div>

    <div class="ci-grid">
        <aside class="ci-sidebar">
            <ul class="ci-tabs">
                @foreach($results as $key => $result)
                    @php $summary = $result['summary']; @endphp
                    <li>
                        <a class="ci-tab {{ $key === $active ? 'is-active' : '' }}" href="{{ route('config_inspector.index', ['tab' => $key]) }}">
                            <span class="ci-tab-main"><i class="{{ $result['icon'] }}"></i>{{ $result['label'] }}</span>
                            <span class="ci-tab-desc">{{ $result['description'] }}</span>
                            <span class="ci-mini-badges">
                                @foreach(['critical','error','warning'] as $sev)
                                    @if(($summary[$sev] ?? 0) > 0)
                                        <span class="ci-badge ci-badge--{{ $sev }}">{{ $summary[$sev] }}</span>
                                    @endif
                                @endforeach
                                <span class="ci-badge ci-badge--success">{{ $summary['score'] ?? 0 }}%</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>

        <section class="ci-panel">
            <div class="ci-panel-head">
                <div>
                    <h2 class="ci-panel-title"><i class="{{ $activeResult['icon'] ?? 'fa-solid fa-circle-info' }} me-2"></i>{{ $activeResult['label'] ?? 'Inspector' }}</h2>
                    <div class="ci-panel-desc">{{ $activeResult['description'] ?? '' }}</div>
                </div>
                <div class="ci-summary-row">
                    @foreach(['critical','error','warning','info','success'] as $sev)
                        <span class="ci-badge ci-badge--{{ $sev }}" title="{{ $severityLabels[$sev] }}">{{ $activeResult['summary'][$sev] ?? 0 }}</span>
                    @endforeach
                </div>
            </div>

            @if(($active ?? null) === 'modules')
                @php
                    $moduleGroups = collect($activeResult['items'] ?? [])
                        ->groupBy(fn ($item) => data_get($item, 'meta.module', 'Sem modulo'))
                        ->sortKeys();
                @endphp

                <div class="ci-module-groups">
                    @forelse($moduleGroups as $moduleName => $moduleItems)
                        @php
                            $moduleSummary = collect(['critical','error','warning','info','success'])
                                ->mapWithKeys(fn ($sev) => [$sev => $moduleItems->where('severity', $sev)->count()])
                                ->all();
                            $hasProblems = (($moduleSummary['critical'] ?? 0) + ($moduleSummary['error'] ?? 0) + ($moduleSummary['warning'] ?? 0)) > 0;
                        @endphp
                        <details class="ci-module-group" {{ $hasProblems ? 'open' : '' }}>
                            <summary class="ci-module-summary">
                                <span class="ci-module-summary-main">
                                    <span class="ci-module-summary-icon"><i class="fa-solid fa-cube"></i></span>
                                    <span>
                                        <span class="ci-module-summary-title">{{ $moduleName }}</span>
                                        <span class="ci-module-summary-meta">{{ $moduleItems->count() }} checks</span>
                                    </span>
                                </span>
                                <span class="ci-module-summary-badges">
                                    @foreach(['critical','error','warning','info','success'] as $sev)
                                        @if(($moduleSummary[$sev] ?? 0) > 0)
                                            <span class="ci-badge ci-badge--{{ $sev }}" title="{{ $severityLabels[$sev] }}">{{ $moduleSummary[$sev] }}</span>
                                        @endif
                                    @endforeach
                                </span>
                            </summary>
                            <div class="ci-module-items">
                                @foreach($moduleItems as $item)
                                    @include('config-inspector::partials.inspection-item', ['item' => $item, 'severityLabels' => $severityLabels])
                                @endforeach
                            </div>
                        </details>
                    @empty
                        <div class="ci-item ci-item--info">Sem resultados neste inspector.</div>
                    @endforelse
                </div>
            @else
            <div class="ci-items">
                @forelse(($activeResult['items'] ?? []) as $item)
                    <article class="ci-item ci-item--{{ $item['severity'] ?? 'info' }}">
                        <div class="ci-item-top">
                            <div>
                                <div class="ci-item-title">{{ $item['title'] ?? 'Inspection item' }}</div>
                                <div class="ci-item-message">{{ $item['message'] ?? '' }}</div>
                            </div>
                            <span class="ci-badge ci-badge--{{ $item['severity'] ?? 'info' }}">{{ $severityLabels[$item['severity'] ?? 'info'] ?? 'Info' }}</span>
                        </div>

                        @if(!empty($item['suggestion']))
                            <div class="ci-suggestion"><i class="fa-solid fa-lightbulb me-1"></i>{{ $item['suggestion'] }}</div>
                        @endif

                        @if(!empty($item['meta']))
                            <details class="ci-meta">
                                <summary>Detalhes técnicos</summary>
                                <pre class="mb-0 mt-2">{{ json_encode($item['meta'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                            </details>
                        @endif
                    </article>
                @empty
                    <div class="ci-item ci-item--info">Sem resultados neste inspector.</div>
                @endforelse
            </div>
            @endif
        </section>
    </div>
</div>
@endsection
