@extends('layouts.app')

@push('styles')
    @include('areas.multiStore.includes.css')
@endpush

@push('scripts')
    @include('areas.multiStore.includes.js')
@endpush

@section('content')

    <div class="customPanel multistore-dashboard-panel">
        <div class="multistore-dashboard-panel__head">
            <div>
                <span class="multistore-dashboard-panel__eyebrow">Store's dashboard</span>
                <h3>Lojas e PageSpeed Insights</h3>
            </div>
            <a href="{{ route('catalog-manager.stores.index') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-store"></i> Ver lojas
            </a>
        </div>

        @include('catalogmanager::partials.store-insights-ribbon', [
            'stores' => $stores ?? collect(),
            'pageSpeedMetrics' => $pageSpeedMetrics ?? collect(),
            'pageSpeedMetricsByStrategy' => $pageSpeedMetricsByStrategy ?? [],
        ])
    </div>

@endsection
