@php
    $stores = collect($stores ?? []);
    $pageSpeedMetrics = collect($pageSpeedMetrics ?? []);
    $pageSpeedMetricsByStrategy = collect($pageSpeedMetricsByStrategy ?? []);
    $pageSpeedStrategies = [
        'mobile' => ['label' => 'Mobile', 'icon' => 'fa-solid fa-mobile-screen-button'],
        'desktop' => ['label' => 'Desktop', 'icon' => 'fa-solid fa-desktop'],
    ];

    if ($pageSpeedMetricsByStrategy->isEmpty()) {
        $pageSpeedMetricsByStrategy = collect(['mobile' => $pageSpeedMetrics]);
    }

    $storeSettings = function ($store) {
        $settings = json_decode((string) ($store->settings ?? ''), true);
        return is_array($settings) ? $settings : [];
    };

    $storeIcon = function ($store) use ($storeSettings) {
        $settings = $storeSettings($store);
        return !empty($settings['icon']) ? $settings['icon'] : 'fa-solid fa-store';
    };

    $normaliseLogoUrl = function ($value) {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (preg_match('#^(https?:)?//#', $value) || str_starts_with($value, 'data:')) {
            return $value;
        }

        $path = ltrim($value, '/');

        foreach (['storage/', 'admin/', 'modules/', 'assets/', 'uploads/', 'images/'] as $publicPrefix) {
            if (str_starts_with($path, $publicPrefix)) {
                return asset($path);
            }
        }

        return asset('storage/' . $path);
    };

    $storeLogo = function ($store) use ($storeSettings, $normaliseLogoUrl) {
        foreach (['logo_path', 'logo', 'logo_url', 'image', 'image_url', 'brand_logo', 'store_logo'] as $key) {
            $logo = $normaliseLogoUrl($store->{$key} ?? null);

            if ($logo) {
                return $logo;
            }
        }

        $settings = $storeSettings($store);

        foreach (['logo_path', 'logo', 'logo_url', 'image', 'image_url', 'brand_logo', 'store_logo'] as $key) {
            $logo = $normaliseLogoUrl($settings[$key] ?? null);

            if ($logo) {
                return $logo;
            }
        }

        foreach (['branding', 'brand', 'front', 'theme'] as $group) {
            if (!is_array($settings[$group] ?? null)) {
                continue;
            }

            foreach (['logo_path', 'logo', 'logo_url', 'image', 'image_url'] as $key) {
                $logo = $normaliseLogoUrl($settings[$group][$key] ?? null);

                if ($logo) {
                    return $logo;
                }
            }
        }

        return null;
    };

    $storeUrl = function ($store) {
        $domain = trim((string) ($store->domain ?? ''));

        if ($domain === '') {
            return null;
        }

        return str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')
            ? $domain
            : 'https://' . $domain;
    };

    $scoreColor = function ($score) {
        if ($score === null) {
            return '#9ca3af';
        }

        return $score >= 90 ? '#0cce6b' : ($score >= 50 ? '#ffa400' : '#ff4e42');
    };

    $scoreLabel = function ($score) {
        if ($score === null) {
            return '-';
        }

        return (string) $score;
    };

    $formatSeconds = function ($ms, int $decimals = 1) {
        return $ms !== null ? number_format(((int) $ms) / 1000, $decimals, ',', '') . ' s' : '-';
    };

    $formatMs = function ($ms) {
        return $ms !== null ? number_format((int) $ms, 0, ',', ' ') . ' ms' : '-';
    };

    $formatCls = function ($value) {
        if ($value === null) {
            return '-';
        }

        return rtrim(rtrim(number_format(((int) $value) / 1000, 3, ',', ''), '0'), ',');
    };
@endphp

@if($stores->isNotEmpty())
    @once
        <style>
            .catalog-store-insights {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
                width: 100%;
            }

            .catalog-store-insights[hidden] {
                display: none;
            }

            .catalog-store-insights-switch {
                display: inline-flex;
                gap: 8px;
                flex-wrap: wrap;
                justify-content: center;
                width: 100%;
                margin: 0 0 12px;
            }

            .catalog-store-insights-switch__button {
                min-width: 120px;
                min-height: 38px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                border: 1px solid rgba(15, 23, 42, .14);
                border-radius: 0;
                background: #fff;
                color: #334155;
                padding: 0 12px;
                font-size: 12px;
                font-weight: 900;
            }

            .catalog-store-insights-switch__button i {
                font-size: 14px;
            }

            .catalog-store-insights-switch__button.is-active {
                border-color: rgba(37, 99, 235, .45);
                background: rgba(37, 99, 235, .10);
                color: #1d4ed8;
            }

            .catalog-store-insight {
                min-width: 0;
                display: grid;
                gap: 12px;
                padding: 12px;
                border: 1px solid rgba(15, 23, 42, .09);
                border-radius: 5px;
                background: #fff;
                box-shadow: 0 8px 18px rgba(15, 23, 42, .05);
            }

            .catalog-store-insight__head {
                display: grid;
                grid-template-columns: 46px minmax(0, 1fr);
                gap: 10px;
                align-items: center;
            }

            .catalog-store-insight__logo {
                width: 46px;
                height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                border: 1px solid rgba(15, 23, 42, .08);
                border-radius: 5px;
                background: #f8fafc;
                color: #9a7415;
                font-size: 20px;
            }

            a.catalog-store-insight__logo {
                text-decoration: none;
                transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
            }

            a.catalog-store-insight__logo:hover {
                border-color: rgba(212, 160, 23, .7);
                box-shadow: 0 8px 18px rgba(15, 23, 42, .10);
                transform: translateY(-1px);
            }

            .catalog-store-insight__logo img {
                display: block;
                width: 100%;
                height: 100%;
                object-fit: contain;
                padding: 5px;
            }

            .catalog-store-insight__title {
                min-width: 0;
            }

            .catalog-store-insight__title strong,
            .catalog-store-insight__title small {
                display: block;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .catalog-store-insight__title strong {
                color: #111827;
                font-size: 14px;
                font-weight: 900;
            }

            .catalog-store-insight__title small {
                color: #64748b;
                font-size: 11px;
                font-weight: 750;
            }

            .catalog-store-insight__scores {
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 8px;
            }

            .catalog-psi-score {
                min-width: 0;
                display: grid;
                justify-items: center;
                gap: 6px;
            }

            .catalog-psi-score__donut {
                width: 54px;
                height: 54px;
                display: inline-grid;
                place-items: center;
                border-radius: 50%;
                background: conic-gradient(var(--psi-color) var(--psi-percent), #e5e7eb 0);
                color: #111827;
                font-size: 14px;
                font-weight: 950;
                position: relative;
            }

            .catalog-psi-score__donut::before {
                content: "";
                position: absolute;
                inset: 5px;
                border-radius: 50%;
                background: #fff;
            }

            .catalog-psi-score__donut span {
                position: relative;
                z-index: 1;
            }

            .catalog-psi-score__label {
                max-width: 100%;
                color: #475569;
                font-size: 10px;
                font-weight: 850;
                line-height: 1.15;
                text-align: center;
            }

            .catalog-store-insight__toggle {
                width: 100%;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                min-height: 34px;
                border: 1px solid rgba(15, 23, 42, .12);
                border-radius: 5px;
                background: #fff;
                color: #334155;
                font-size: 12px;
                font-weight: 850;
                transition: border-color .15s ease, color .15s ease, background .15s ease;
            }

            .catalog-store-insight__toggle:hover {
                border-color: rgba(212, 160, 23, .65);
                color: #9a7415;
                background: rgba(212, 160, 23, .06);
            }

            .catalog-store-insight__toggle i {
                transition: transform .15s ease;
            }

            .catalog-store-insight.is-expanded .catalog-store-insight__toggle i {
                transform: rotate(180deg);
            }

            .catalog-store-insight__details {
                display: grid;
                gap: 6px;
                padding-top: 2px;
            }

            .catalog-store-insight__detail {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 7px 0;
                border-top: 1px solid rgba(15, 23, 42, .08);
                color: #475569;
                font-size: 12px;
                font-weight: 750;
            }

            .catalog-store-insight__detail strong {
                color: #111827;
                font-weight: 900;
                white-space: nowrap;
            }

            .catalog-store-insight__detail span {
                min-width: 0;
                overflow-wrap: anywhere;
            }

            @media (max-width: 575.98px) {
                .catalog-store-insights {
                    grid-template-columns: minmax(0, 1fr);
                }

                .catalog-store-insight__scores {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (min-width: 576px) and (max-width: 991.98px) {
                .catalog-store-insights {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (min-width: 992px) and (max-width: 1399.98px) {
                .catalog-store-insights {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }
            }
        </style>

        <script>
            document.addEventListener('click', function (event) {
                const strategyButton = event.target.closest('[data-store-insight-strategy]');

                if (strategyButton) {
                    const strategy = strategyButton.dataset.storeInsightStrategy;
                    const wrapper = strategyButton.closest('[data-store-insight-wrapper]');

                    if (!wrapper) {
                        return;
                    }

                    wrapper.querySelectorAll('[data-store-insight-strategy]').forEach((button) => {
                        button.classList.toggle('is-active', button.dataset.storeInsightStrategy === strategy);
                    });

                    wrapper.querySelectorAll('[data-store-insight-strategy-panel]').forEach((panel) => {
                        panel.hidden = panel.dataset.storeInsightStrategyPanel !== strategy;
                    });

                    return;
                }

                const toggle = event.target.closest('[data-store-insight-toggle]');

                if (!toggle) {
                    return;
                }

                const card = toggle.closest('.catalog-store-insight');
                const details = card ? card.querySelector('.catalog-store-insight__details') : null;
                const expanded = toggle.getAttribute('aria-expanded') === 'true';

                if (!card || !details) {
                    return;
                }

                card.classList.toggle('is-expanded', !expanded);
                toggle.setAttribute('aria-expanded', expanded ? 'false' : 'true');
                details.hidden = expanded;
            });
        </script>
    @endonce

    <div data-store-insight-wrapper>
        <div class="catalog-store-insights-switch">
            @foreach($pageSpeedStrategies as $strategy => $strategyMeta)
                <button
                    type="button"
                    class="catalog-store-insights-switch__button {{ $strategy === 'mobile' ? 'is-active' : '' }}"
                    data-store-insight-strategy="{{ $strategy }}"
                >
                    <i class="{{ $strategyMeta['icon'] }}"></i>
                    <span>{{ $strategyMeta['label'] }}</span>
                </button>
            @endforeach
        </div>

        @foreach($pageSpeedStrategies as $strategy => $strategyMeta)
            @php
                $strategyMetrics = collect($pageSpeedMetricsByStrategy->get($strategy, collect()));
            @endphp
            <div class="catalog-store-insights" data-store-insight-strategy-panel="{{ $strategy }}" @if($strategy !== 'mobile') hidden @endif>
                @foreach($stores as $store)
                    @php
                        $metric = $strategyMetrics->get($store->id);
                        $logo = $storeLogo($store);
                        $url = $storeUrl($store);
                        $scores = [
                            ['label' => 'Desempenho', 'value' => $metric?->performance_score],
                            ['label' => 'Acessibilidade', 'value' => $metric?->accessibility_score],
                            ['label' => 'Praticas recomendadas', 'value' => $metric?->best_practices_score],
                            ['label' => 'SEO', 'value' => $metric?->seo_score],
                        ];
                    @endphp

                    <article class="catalog-store-insight">
                        <div class="catalog-store-insight__head">
                            @if($url)
                                <a class="catalog-store-insight__logo" href="{{ $url }}" target="_blank" rel="noopener noreferrer" title="Abrir {{ $store->name }}">
                                    @if($logo)
                                        <img src="{{ $logo }}" alt="{{ $store->name }}">
                                    @else
                                        <i class="{{ $storeIcon($store) }}"></i>
                                    @endif
                                </a>
                            @else
                                <span class="catalog-store-insight__logo">
                                    @if($logo)
                                        <img src="{{ $logo }}" alt="{{ $store->name }}">
                                    @else
                                        <i class="{{ $storeIcon($store) }}"></i>
                                    @endif
                                </span>
                            @endif
                            <span class="catalog-store-insight__title">
                                <strong>{{ $store->name }}</strong>
                                <small>
                                    @if(($metric?->status ?? null) === 'completed')
                                        PageSpeed Insights de hoje - {{ $strategyMeta['label'] }}
                                    @elseif(($metric?->status ?? null) === 'failed')
                                        Teste {{ strtolower($strategyMeta['label']) }} falhou
                                    @elseif(($metric?->status ?? null) === 'skipped')
                                        Sem dominio configurado
                                    @else
                                        Pendente hoje - {{ $strategyMeta['label'] }}
                                    @endif
                                </small>
                            </span>
                        </div>

                        <div class="catalog-store-insight__scores">
                            @foreach($scores as $score)
                                @php
                                    $value = $score['value'];
                                    $percent = $value !== null ? max(0, min(100, (int) $value)) : 0;
                                @endphp
                                <div class="catalog-psi-score">
                                    <span class="catalog-psi-score__donut" style="--psi-percent: {{ $percent }}%; --psi-color: {{ $scoreColor($value) }};">
                                        <span>{{ $scoreLabel($value) }}</span>
                                    </span>
                                    <span class="catalog-psi-score__label">{{ $score['label'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="catalog-store-insight__toggle" data-store-insight-toggle aria-expanded="false">
                            <span>Ver metricas</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>

                        <div class="catalog-store-insight__details" hidden>
                            <div class="catalog-store-insight__detail">
                                <span>First Contentful Paint</span>
                                <strong>{{ $formatSeconds($metric?->first_contentful_paint_ms) }}</strong>
                            </div>
                            <div class="catalog-store-insight__detail">
                                <span>Largest Contentful Paint</span>
                                <strong>{{ $formatSeconds($metric?->largest_contentful_paint_ms) }}</strong>
                            </div>
                            <div class="catalog-store-insight__detail">
                                <span>Total Blocking Time</span>
                                <strong>{{ $formatMs($metric?->total_blocking_time_ms) }}</strong>
                            </div>
                            <div class="catalog-store-insight__detail">
                                <span>Cumulative Layout Shift</span>
                                <strong>{{ $formatCls($metric?->cumulative_layout_shift) }}</strong>
                            </div>
                            <div class="catalog-store-insight__detail">
                                <span>Speed Index</span>
                                <strong>{{ $formatSeconds($metric?->speed_index_ms) }}</strong>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endforeach
    </div>
@endif
