@extends(config('productivitymanager.layout', 'layouts.app'))

@section('content')
<div class="productivity-manager-page">
    @include('productivitymanager::Includes.css')

    <div class="productivity-manager-shell">
        @include('productivitymanager::Includes._components.header')

        <div class="productivity-manager-card">
            <h2 class="productivity-card-title">Module Configuration</h2>

            <div class="productivity-grid">
                <div class="productivity-meta">
                    <div class="productivity-meta__label">Refresh seconds</div>
                    <div>{{ $config['refresh_seconds'] ?? 30 }}</div>
                </div>

                <div class="productivity-meta">
                    <div class="productivity-meta__label">Today limit</div>
                    <div>{{ $config['today_limit'] ?? 5 }}</div>
                </div>

                <div class="productivity-meta">
                    <div class="productivity-meta__label">Alert limit</div>
                    <div>{{ $config['alert_limit'] ?? 8 }}</div>
                </div>

                <div class="productivity-meta">
                    <div class="productivity-meta__label">Allowed webhook sources</div>
                    <div>{{ implode(', ', $config['allowed_webhook_sources'] ?? []) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
