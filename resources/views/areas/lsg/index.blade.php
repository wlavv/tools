@extends('layouts.app')

@push('styles')
    <style>
        .lsg-group-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px; margin-bottom: 12px; }
        .lsg-group-card { border: 1px solid var(--border-soft, rgba(148, 163, 184, .22)); background: var(--bg-panel, var(--card-bg, #fff)); color: var(--text-primary, #111827); padding: 16px; }
        .lsg-group-card__head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; margin-bottom: 12px; }
        .lsg-group-card__title { display: flex; gap: 10px; align-items: center; }
        .lsg-group-card__icon { width: 38px; height: 38px; display: grid; place-items: center; background: rgba(37, 99, 235, .12); color: #2563eb; }
        .lsg-group-card h3 { font-size: 1rem; margin: 0; }
        .lsg-group-card__count { text-align: right; }
        .lsg-group-card__count strong { display: block; font-size: 1.35rem; line-height: 1; }
        .lsg-site-list { display: grid; gap: 8px; }
        .lsg-site-item { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; align-items: center; border: 1px solid var(--border-soft, rgba(148, 163, 184, .18)); padding: 10px; background: var(--bg-panel-soft, rgba(148, 163, 184, .05)); color: var(--text-primary, #111827); }
        .lsg-site-item.has-project-link { cursor: pointer; transition: border-color .15s ease, background .15s ease; }
        .lsg-site-item.has-project-link:hover { border-color: rgba(37, 99, 235, .42); background: rgba(37, 99, 235, .08); }
        .lsg-site-item strong, .lsg-site-item small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .lsg-site-actions { display: flex; gap: 6px; align-items: center; }
        .lsg-badge { display: inline-flex; align-items: center; gap: 6px; border: 1px solid rgba(148, 163, 184, .24); padding: 3px 7px; font-size: .76rem; font-weight: 700; }
        .lsg-badge.is-score { border-color: color-mix(in srgb, var(--score-color) 58%, transparent); background: color-mix(in srgb, var(--score-color) 16%, transparent); color: var(--score-color); }
        button.lsg-badge { cursor: pointer; font-family: inherit; }
        button.lsg-badge:hover { border-color: color-mix(in srgb, var(--score-color, #2563eb) 76%, transparent); background: color-mix(in srgb, var(--score-color, #2563eb) 24%, transparent); color: var(--score-color, #1d4ed8); }
        .lsg-site-action { min-height: 26px; display: inline-flex; align-items: center; justify-content: center; padding: 3px 7px; font-size: .76rem; line-height: 1; border-radius: 0; }
        .lsg-badge.is-store { color: #065f46; background: #d1fae5; }
        .lsg-badge.is-domain { color: #581c87; background: #f3e8ff; }
        .lsg-empty { opacity: .66; font-size: .86rem; padding: 8px 0; }

        .lsg-modal-backdrop { position: fixed; inset: 0; display: none; align-items: center; justify-content: center; padding: 20px; background: rgba(15, 23, 42, .48); z-index: 1050; }
        .lsg-modal-backdrop.is-open { display: flex; }
        .lsg-modal { width: min(720px, 100%); max-height: calc(100vh - 40px); overflow: auto; border: 1px solid var(--border-soft, rgba(148, 163, 184, .28)); background: var(--bg-panel, var(--card-bg, #fff)); color: var(--text-primary, #111827); box-shadow: 0 22px 55px rgba(15, 23, 42, .24); }
        .lsg-modal__head { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; padding: 14px 16px; border-bottom: 1px solid rgba(148, 163, 184, .18); }
        .lsg-modal__head h3 { margin: 0; font-size: 1rem; }
        .lsg-modal__head span { display: block; color: #64748b; font-size: .82rem; font-weight: 700; margin-top: 3px; }
        .lsg-modal__close { border: 1px solid rgba(148, 163, 184, .24); background: transparent; width: 34px; height: 34px; display: grid; place-items: center; }
        .lsg-modal__body { padding: 16px; display: grid; gap: 12px; }
        .catalog-store-insights-switch { display: inline-flex; gap: 8px; flex-wrap: wrap; justify-content: center; width: 100%; margin: 0 0 12px; }
        .catalog-store-insights-switch__button { min-width: 120px; min-height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid rgba(15, 23, 42, .14); border-radius: 0; background: #fff; color: #334155; padding: 0 12px; font-size: 12px; font-weight: 900; }
        .catalog-store-insights-switch__button i { font-size: 14px; }
        .catalog-store-insights-switch__button.is-active { border-color: rgba(37, 99, 235, .45); background: #dbeafe; color: #1e3a8a; }
        .catalog-store-insights-switch__button:disabled { cursor: not-allowed; opacity: .45; }

        .catalog-store-insight {
            min-width: 0;
            display: grid;
            gap: 12px;
            padding: 12px;
            border: 1px solid var(--border-soft, rgba(15, 23, 42, .09));
            border-radius: 5px;
            background: var(--bg-panel-soft, #fff);
            color: var(--text-primary, #111827);
            box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
        }
        .catalog-store-insight__head { display: grid; grid-template-columns: 46px minmax(0, 1fr); gap: 10px; align-items: center; }
        .catalog-store-insight__logo { width: 46px; height: 46px; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid rgba(15, 23, 42, .08); border-radius: 5px; background: #f8fafc; color: #6b4f0f; font-size: 20px; text-decoration: none; }
        a.catalog-store-insight__logo:hover { border-color: rgba(212, 160, 23, .7); box-shadow: 0 8px 18px rgba(15, 23, 42, .10); }
        .catalog-store-insight__title { min-width: 0; }
        .catalog-store-insight__title strong, .catalog-store-insight__title small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .catalog-store-insight__title strong { color: var(--text-primary, #111827); font-size: 14px; font-weight: 900; }
        .catalog-store-insight__title small { color: var(--text-muted, #64748b); font-size: 11px; font-weight: 750; }
        .catalog-store-insight__scores { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
        .catalog-psi-score { min-width: 0; display: grid; justify-items: center; gap: 6px; }
        .catalog-psi-score__donut { width: 54px; height: 54px; display: inline-grid; place-items: center; border-radius: 50%; background: conic-gradient(var(--psi-color) var(--psi-percent), #e5e7eb 0); color: #111827; font-size: 14px; font-weight: 950; position: relative; }
        .catalog-psi-score__donut::before { content: ""; position: absolute; inset: 5px; border-radius: 50%; background: #fff; }
        .catalog-psi-score__donut span { position: relative; z-index: 1; }
        .catalog-psi-score__label { max-width: 100%; color: #475569; font-size: 10px; font-weight: 850; line-height: 1.15; text-align: center; }
        .catalog-store-insight__toggle { width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 34px; border: 1px solid rgba(15, 23, 42, .12); border-radius: 5px; background: #fff; color: #334155; font-size: 12px; font-weight: 850; transition: border-color .15s ease, color .15s ease, background .15s ease; }
        .catalog-store-insight__toggle:hover { border-color: rgba(212, 160, 23, .65); color: #713f12; background: #fef3c7; }
        .catalog-store-insight__toggle i { transition: transform .15s ease; }
        .catalog-store-insight.is-expanded .catalog-store-insight__toggle i { transform: rotate(180deg); }
        .catalog-store-insight__details { display: grid; gap: 6px; padding-top: 2px; }
        .catalog-store-insight__details[hidden] { display: none; }
        .catalog-store-insight__detail { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; border-top: 1px solid var(--border-soft, rgba(15, 23, 42, .08)); color: var(--text-muted, #475569); font-size: 12px; font-weight: 750; }
        .catalog-store-insight__detail strong { color: var(--text-primary, #111827); font-weight: 900; white-space: nowrap; }
        .catalog-store-insight__detail span { min-width: 0; overflow-wrap: anywhere; }
        .lsg-modal-error { display: none; border: 1px solid rgba(220, 38, 38, .22); background: #fee2e2; color: #7f1d1d; padding: 10px; font-weight: 750; white-space: pre-wrap; overflow-wrap: anywhere; }

        @media (max-width: 767.98px) {
            .catalog-store-insight__scores { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
@endpush

@section('content')
    @php
        $pageSpeedMetricsByStrategy = collect($pageSpeedMetricsByStrategy ?? []);
        $mobileMetrics = collect($pageSpeedMetricsByStrategy->get('mobile', collect()));
        $desktopMetrics = collect($pageSpeedMetricsByStrategy->get('desktop', collect()));
        $scoreColor = fn ($score) => $score === null ? '#9ca3af' : ($score >= 90 ? '#0cce6b' : ($score >= 50 ? '#ffa400' : '#ff4e42'));
        $formatSeconds = fn ($ms) => $ms !== null ? number_format(((int) $ms) / 1000, 1, ',', '') . ' s' : '-';
        $formatMs = fn ($ms) => $ms !== null ? number_format((int) $ms, 0, ',', ' ') . ' ms' : '-';
        $formatCls = function ($value) {
            if ($value === null) return '-';
            return rtrim(rtrim(number_format(((int) $value) / 1000, 3, ',', ''), '0'), ',');
        };
        $siteUrl = function ($site) {
            $domain = trim((string) ($site->domain ?? ''));
            if ($domain === '') return null;
            return str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://') ? $domain : 'https://' . $domain;
        };
        $siteIcon = function ($site) {
            $settings = json_decode((string) ($site->settings ?? ''), true);
            return is_array($settings) && !empty($settings['icon']) ? $settings['icon'] : (($site->record_type ?? 'store') === 'store' ? 'fa-solid fa-store' : 'fa-solid fa-globe');
        };
        $metricPayload = function ($site, $metric, string $strategy) use ($formatSeconds, $formatMs, $formatCls, $siteUrl, $siteIcon) {
            if (!$metric) {
                return null;
            }

            return [
                'site' => $site->name,
                'domain' => $site->domain,
                'url' => $siteUrl($site),
                'icon' => $siteIcon($site),
                'strategy' => ucfirst($strategy),
                'status' => $metric->status ?? 'pending',
                'scores' => [
                    ['label' => 'Desempenho', 'value' => $metric->performance_score ?? null],
                    ['label' => 'Acessibilidade', 'value' => $metric->accessibility_score ?? null],
                    ['label' => 'Praticas recomendadas', 'value' => $metric->best_practices_score ?? null],
                    ['label' => 'SEO', 'value' => $metric->seo_score ?? null],
                ],
                'metrics' => [
                    'First Contentful Paint' => $formatSeconds($metric->first_contentful_paint_ms ?? null),
                    'Largest Contentful Paint' => $formatSeconds($metric->largest_contentful_paint_ms ?? null),
                    'Total Blocking Time' => $formatMs($metric->total_blocking_time_ms ?? null),
                    'Cumulative Layout Shift' => $formatCls($metric->cumulative_layout_shift ?? null),
                    'Speed Index' => $formatSeconds($metric->speed_index_ms ?? null),
                ],
                'error' => $metric->error_message ?? null,
            ];
        };
    @endphp

    <div class="lsg-group-grid">
        @foreach($groups as $group)
            <section class="lsg-group-card">
                <div class="lsg-group-card__head">
                    <div class="lsg-group-card__title">
                        <div class="lsg-group-card__icon"><i class="{{ $group['icon'] }}"></i></div>
                        <div><h3>{{ $group['label'] }}</h3></div>
                    </div>
                    <div class="lsg-group-card__count">
                        <strong>{{ $group['count'] }}</strong>
                        <span class="text-muted small">{{ $group['active'] }} ativos</span>
                    </div>
                </div>

                <div class="lsg-site-list">
                    @forelse($group['sites'] as $site)
                        @php
                            $isStore = ($site->record_type ?? 'store') === 'store';
                            $mobileMetric = $mobileMetrics->get($site->id);
                            $desktopMetric = $desktopMetrics->get($site->id);
                            $averageValues = collect([$mobileMetric?->performance_score, $desktopMetric?->performance_score])->filter(fn ($value) => $value !== null);
                            $averageScore = $averageValues->isNotEmpty() ? (int) round($averageValues->avg()) : null;
                            $averageScoreColor = $scoreColor($averageScore);
                            $payload = [
                                'defaultStrategy' => $mobileMetric ? 'mobile' : 'desktop',
                                'strategies' => [
                                    'mobile' => $metricPayload($site, $mobileMetric, 'mobile'),
                                    'desktop' => $metricPayload($site, $desktopMetric, 'desktop'),
                                ],
                            ];
                        @endphp
                        <div
                            class="lsg-site-item {{ !empty($site->project_url) ? 'has-project-link' : '' }}"
                            @if(!empty($site->project_url))
                                data-lsg-project-url="{{ $site->project_url }}"
                                role="link"
                                tabindex="0"
                                title="Abrir projeto {{ $site->project_name ?? $site->name }}"
                            @endif
                        >
                            <div>
                                <strong>{{ $site->name }}</strong>
                                <small class="text-muted">{{ $site->domain ?: '-' }}</small>
                            </div>
                            <div class="lsg-site-actions">
                                @if($averageScore !== null)
                                    <button
                                        type="button"
                                        class="lsg-badge is-score"
                                        style="--score-color: {{ $averageScoreColor }};"
                                        title="Ver detalhe PageSpeed"
                                        data-lsg-pagespeed='@json($payload)'
                                    >
                                        {{ $averageScore }}
                                    </button>
                                @endif
                                <a href="{{ route('catalog-manager.stores.edit', $site->id) }}" class="btn btn-sm btn-outline-warning lsg-site-action" title="Editar">
                                    <i class="fa-solid fa-pencil"></i>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="lsg-empty">Sem sites nesta area.</div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>

    <div class="lsg-modal-backdrop" id="lsgPageSpeedModal" aria-hidden="true">
        <div class="lsg-modal" role="dialog" aria-modal="true" aria-labelledby="lsgPageSpeedModalTitle">
            <div class="lsg-modal__head">
                <div>
                    <h3 id="lsgPageSpeedModalTitle">PageSpeed Insights</h3>
                    <span id="lsgPageSpeedModalSubtitle"></span>
                </div>
                <button type="button" class="lsg-modal__close" data-lsg-modal-close aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="lsg-modal__body">
                <div class="catalog-store-insights-switch">
                    <button type="button" class="catalog-store-insights-switch__button" data-lsg-strategy="mobile"><i class="fa-solid fa-mobile-screen-button"></i><span>Mobile</span></button>
                    <button type="button" class="catalog-store-insights-switch__button" data-lsg-strategy="desktop"><i class="fa-solid fa-desktop"></i><span>Desktop</span></button>
                </div>
                <div id="lsgPageSpeedArticle"></div>
                <div class="lsg-modal-error" id="lsgPageSpeedError"></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('lsgPageSpeedModal');
            const articleTarget = document.getElementById('lsgPageSpeedArticle');
            const errorTarget = document.getElementById('lsgPageSpeedError');
            let currentPayload = null;

            function scoreColor(score) {
                if (score === null || score === undefined) return '#9ca3af';
                return score >= 90 ? '#0cce6b' : (score >= 50 ? '#ffa400' : '#ff4e42');
            }

            function renderStrategy(strategy) {
                if (!currentPayload || !modal) return;

                const data = currentPayload.strategies[strategy];
                modal.querySelectorAll('[data-lsg-strategy]').forEach((button) => {
                    const active = button.dataset.lsgStrategy === strategy;
                    button.classList.toggle('is-active', active);
                    button.disabled = !currentPayload.strategies[button.dataset.lsgStrategy];
                });

                if (!data) {
                    articleTarget.innerHTML = '';
                    errorTarget.textContent = 'Sem dados PageSpeed para esta estrategia.';
                    errorTarget.style.display = 'block';
                    return;
                }

                document.getElementById('lsgPageSpeedModalTitle').textContent = data.site || 'PageSpeed Insights';
                document.getElementById('lsgPageSpeedModalSubtitle').textContent = `${data.strategy || '-'} | ${data.domain || '-'} | ${data.status || '-'}`;

                const logo = data.url
                    ? `<a class="catalog-store-insight__logo" href="${data.url}" target="_blank" rel="noopener noreferrer" title="Abrir ${data.site || ''}"><i class="${data.icon || 'fa-solid fa-globe'}"></i></a>`
                    : `<span class="catalog-store-insight__logo"><i class="${data.icon || 'fa-solid fa-globe'}"></i></span>`;

                const scores = (data.scores || []).map((score) => {
                    const value = score.value;
                    const percent = value === null || value === undefined ? 0 : Math.max(0, Math.min(100, parseInt(value, 10)));
                    return `
                        <div class="catalog-psi-score">
                            <span class="catalog-psi-score__donut" style="--psi-percent: ${percent}%; --psi-color: ${scoreColor(value)};">
                                <span>${value ?? '-'}</span>
                            </span>
                            <span class="catalog-psi-score__label">${score.label}</span>
                        </div>
                    `;
                }).join('');

                const metrics = Object.entries(data.metrics || {}).map(([label, value]) => `
                    <div class="catalog-store-insight__detail">
                        <span>${label}</span>
                        <strong>${value ?? '-'}</strong>
                    </div>
                `).join('');

                articleTarget.innerHTML = `
                    <article class="catalog-store-insight">
                        <div class="catalog-store-insight__head">
                            ${logo}
                            <span class="catalog-store-insight__title">
                                <strong>${data.site || '-'}</strong>
                                <small>PageSpeed Insights de hoje - ${data.strategy || '-'}</small>
                            </span>
                        </div>
                        <div class="catalog-store-insight__scores">${scores}</div>
                        <button type="button" class="catalog-store-insight__toggle" data-lsg-modal-metrics-toggle aria-expanded="false">
                            <span>Ver metricas</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                        <div class="catalog-store-insight__details" hidden>${metrics}</div>
                    </article>
                `;

                errorTarget.textContent = data.error || '';
                errorTarget.style.display = data.error ? 'block' : 'none';
            }

            document.addEventListener('click', function (event) {
                const close = event.target.closest('[data-lsg-modal-close]');
                const strategyButton = event.target.closest('[data-lsg-strategy]');
                const trigger = event.target.closest('[data-lsg-pagespeed]');

                if (close || event.target === modal) {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                    return;
                }

                if (strategyButton) {
                    if (strategyButton.disabled) {
                        return;
                    }
                    renderStrategy(strategyButton.dataset.lsgStrategy);
                    return;
                }

                const metricsToggle = event.target.closest('[data-lsg-modal-metrics-toggle]');

                if (metricsToggle) {
                    const card = metricsToggle.closest('.catalog-store-insight');
                    const details = card ? card.querySelector('.catalog-store-insight__details') : null;
                    const expanded = metricsToggle.getAttribute('aria-expanded') === 'true';

                    if (!card || !details) {
                        return;
                    }

                    card.classList.toggle('is-expanded', !expanded);
                    metricsToggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                    details.hidden = expanded;
                    return;
                }

                if (trigger) {
                    currentPayload = JSON.parse(trigger.dataset.lsgPagespeed || '{}');
                    const strategy = currentPayload.defaultStrategy || 'mobile';
                    renderStrategy(strategy);
                    modal.classList.add('is-open');
                    modal.setAttribute('aria-hidden', 'false');
                    return;
                }

                if (event.target.closest('a, button')) return;

                const projectRow = event.target.closest('[data-lsg-project-url]');
                if (projectRow && projectRow.dataset.lsgProjectUrl) {
                    window.location.href = projectRow.dataset.lsgProjectUrl;
                }
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    modal.classList.remove('is-open');
                    modal.setAttribute('aria-hidden', 'true');
                    return;
                }

                if (!['Enter', ' '].includes(event.key)) return;

                const projectRow = event.target.closest('[data-lsg-project-url]');
                if (projectRow && projectRow.dataset.lsgProjectUrl) {
                    event.preventDefault();
                    window.location.href = projectRow.dataset.lsgProjectUrl;
                }
            });
        });
    </script>
@endpush
