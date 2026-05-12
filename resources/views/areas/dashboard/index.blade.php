@extends('layouts.app')

@push('styles')
    @include('areas.dashboard.includes.css')
@endpush

@push('scripts')
    @include('areas.dashboard.includes.js')
@endpush

@section('content')
    @php
        $hour = (int) now()->format('H');
        $greeting = 'Welcome';

        if ($hour >= 5 && $hour < 12) {
            $greeting = 'Good morning';
        } elseif ($hour >= 12 && $hour < 19) {
            $greeting = 'Good afternoon';
        } else {
            $greeting = 'Good evening';
        }

        $userName = auth()->user()->name ?? 'User';
        $todayLabel = now()->format('l, d F Y');

        $heroStats = $heroStats ?? [
            ['label' => 'Areas', 'value' => 8, 'icon' => 'fa-solid fa-grid-2'],
            ['label' => 'Modules', 'value' => 12, 'icon' => 'fa-solid fa-cubes'],
            ['label' => 'Shortcuts', 'value' => isset($accessList) ? count($accessList) : 0, 'icon' => 'fa-solid fa-bolt'],
        ];

        $heroActions = $heroActions ?? [
            ['label' => 'Administration', 'icon' => 'fa-solid fa-screwdriver-wrench', 'url' => route('administration.index')],
            ['label' => 'Web', 'icon' => 'fa-solid fa-globe', 'url' => route('web.index')],
            ['label' => 'Finance', 'icon' => 'fa-solid fa-chart-line', 'url' => route('finance.index')],
            ['label' => 'Marketing', 'icon' => 'fa-solid fa-bullhorn', 'url' => route('marketing.index')],
            ['label' => 'Sales', 'icon' => 'fa-solid fa-basket-shopping', 'url' => route('sales.index')],
        ];

        $weather = $weather ?? [
            'location' => 'Viana do Castelo - Cidade',
            'temp' => 15,
            'description' => 'Poucas nuvens',
            'image' => asset('/modules/tasks/weather/cloudy-1-day.svg'),
        ];

        $dailyQuote = $dailyQuote ?? [
            'quote' => 'Hoje não precisa ser perfeito. Precisa apenas avançar.',
            'author' => 'LS Group',
        ];
    @endphp

    <div class="lsg-hero-card mb-4">
        <div class="lsg-hero-bg">
            <div class="lsg-hero-orb orb-1"></div>
            <div class="lsg-hero-orb orb-2"></div>
            <div class="lsg-hero-grid"></div>
        </div>

        <div class="lsg-hero-inner">
            <div class="row g-4 align-items-stretch">
                <div class="col-12 col-xl-6">
                    <div class="lsg-hero-copy h-100">
                        @if(!empty($weather))
                            <div class="lsg-hero-weather">
                                <div class="lsg-hero-weather-inner">
                                    <div class="lsg-hero-weather-meta">
                                        <span class="lsg-hero-weather-temp">{{ $weather['temp'] ?? '--' }}°</span>
                                        @if(!empty($weather['location']))
                                            <span class="lsg-hero-weather-location">{{ $weather['location'] }}</span>
                                        @endif
                                    </div>
                                    
                                    @if(!empty($weather['image']))
                                        <img src="{{ $weather['image'] }}" alt="{{ $weather['description'] ?? 'Weather' }}" class="lsg-hero-weather-icon">
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div style="margin-top: -20px;">
                            <div class="lsg-hero-badge">
                                <i class="fa-solid fa-sparkles me-2"></i>
                                <span>LS Group Workspace</span>
                            </div>

                            <div class="lsg-hero-date">{{ $todayLabel }}</div>

                            <h1 class="lsg-hero-title" style="margin-top: 70px">{{ $greeting }}, {{ $userName }}</h1>

                            <p class="lsg-hero-text">
                                Welcome to <strong>Webtools Manager</strong>, your central control hub for the LS Group ecosystem.
                                Access your operational areas, monitor priorities and jump directly into your main workflows.
                            </p>

                            @if(!empty($dailyQuote['quote']))
                                <div class="lsg-hero-quote" style="margin-top: 50px">
                                    <div class="lsg-hero-quote-body">
                                        <div class="lsg-hero-quote-icon">
                                            <i class="fa-solid fa-quote-left"></i>
                                        </div>

                                        <div class="lsg-hero-quote-content">
                                            <div class="lsg-hero-quote-text">
                                                {{ $dailyQuote['quote'] }}
                                            </div>

                                            @if(!empty($dailyQuote['author']))
                                                <div class="lsg-hero-quote-author">
                                                    {{ $dailyQuote['author'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-6">
                    <div class="row g-4 h-100">
                        <div class="col-12 col-md-6">
                            <div class="lsg-hero-panel h-100">
                                <div class="lsg-hero-panel-title">Overview</div>

                                <div class="lsg-hero-stats">
                                    @foreach($heroStats as $stat)
                                        <div class="lsg-hero-stat">
                                            <div class="lsg-hero-stat-icon">
                                                <i class="{{ $stat['icon'] }}"></i>
                                            </div>
                                            <div class="lsg-hero-stat-body">
                                                <div class="lsg-hero-stat-value">{{ $stat['value'] }}</div>
                                                <div class="lsg-hero-stat-label">{{ $stat['label'] }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="lsg-hero-panel h-100">
                                <div class="lsg-hero-panel-title">Quick Access</div>

                                <div class="lsg-hero-actions">
                                    @foreach($heroActions as $action)
                                        <a href="{{ $action['url'] }}" class="lsg-hero-action">
                                            <span class="lsg-hero-action-icon">
                                                <i class="{{ $action['icon'] }}"></i>
                                            </span>
                                            <span class="lsg-hero-action-label">{{ $action['label'] }}</span>
                                            <span class="lsg-hero-action-arrow">
                                                <i class="fa-solid fa-chevron-right"></i>
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
