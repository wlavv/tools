@extends('site-manager::layouts.module')

@section('module-content')
    @php
        $runsByStrategy = $site->pageSpeedRuns->groupBy('strategy');
        $desktopRun = $runsByStrategy->get('desktop')?->first();
        $mobileRun = $runsByStrategy->get('mobile')?->first();
        $scoreColor = function ($score) {
            if ($score === null) {
                return '#94a3b8';
            }

            return $score >= 90 ? '#22c55e' : ($score >= 50 ? '#f59e0b' : '#ef4444');
        };
        $scoreFields = ['performance_score' => 'Perf.', 'accessibility_score' => 'Acess.', 'best_practices_score' => 'Boas prat.', 'seo_score' => 'SEO'];
    @endphp

    <div class="sm-site-overview mb-3">
        <div class="sm-pagespeed-card">
            <div class="d-flex justify-content-between gap-2 mb-3">
                <h5 class="mb-0">Desktop</h5>
                <span class="sm-badge">{{ $desktopRun?->status ?? '-' }}</span>
            </div>
            @if($desktopRun)
                <div class="sm-pagespeed-scores">
                    @foreach($scoreFields as $field => $label)
                        @php
                            $score = $desktopRun->{$field};
                            $percent = $score === null ? 0 : max(0, min(100, (int) $score));
                        @endphp
                        <div class="sm-psi-donut-wrap">
                            <div class="sm-psi-donut" style="--psi-percent: {{ $percent }}%; --psi-color: {{ $scoreColor($score) }};">
                                <span>{{ $score === null ? '-' : $score . '%' }}</span>
                            </div>
                            <small>{{ $label }}</small>
                        </div>
                    @endforeach
                </div>
                @if($desktopRun->error_message)<p class="text-danger mt-3 mb-0">{{ $desktopRun->error_message }}</p>@endif
            @else
                <div class="text-muted">Sem resultado desktop.</div>
            @endif
        </div>

        <div class="sm-card sm-site-summary">
            <strong>{{ $site->name }}</strong>
            @if($site->resolved_url)
                <a class="sm-site-summary__url" href="{{ $site->resolved_url }}" target="_blank" rel="noopener noreferrer">
                    {{ $site->resolved_url }}
                </a>
            @else
                <span>-</span>
            @endif
            <div class="sm-site-summary__badges">
                <span class="sm-badge">{{ config('site-manager.site_types.' . $site->site_type, $site->site_type) }}</span>
                <span class="sm-badge">{{ $site->environment }}</span>
                <span class="sm-badge">{{ $site->status }}</span>
            </div>
        </div>

        <div class="sm-pagespeed-card">
            <div class="d-flex justify-content-between gap-2 mb-3">
                <h5 class="mb-0">Mobile</h5>
                <span class="sm-badge">{{ $mobileRun?->status ?? '-' }}</span>
            </div>
            @if($mobileRun)
                <div class="sm-pagespeed-scores">
                    @foreach($scoreFields as $field => $label)
                        @php
                            $score = $mobileRun->{$field};
                            $percent = $score === null ? 0 : max(0, min(100, (int) $score));
                        @endphp
                        <div class="sm-psi-donut-wrap">
                            <div class="sm-psi-donut" style="--psi-percent: {{ $percent }}%; --psi-color: {{ $scoreColor($score) }};">
                                <span>{{ $score === null ? '-' : $score . '%' }}</span>
                            </div>
                            <small>{{ $label }}</small>
                        </div>
                    @endforeach
                </div>
                @if($mobileRun->error_message)<p class="text-danger mt-3 mb-0">{{ $mobileRun->error_message }}</p>@endif
            @else
                <div class="text-muted">Sem resultado mobile.</div>
            @endif
        </div>
    </div>

    <div class="sm-card">
        @if($site->resolved_url)
            <div class="sm-site-preview">
                <iframe src="{{ $site->resolved_url }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        @else
            <div class="text-muted">Define um dominio ou URL publica para ativar o preview.</div>
        @endif
    </div>
@endsection
