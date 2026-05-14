@extends('module-health::layouts.module')

@section('content')
@include('module-health::partials.styles')

<div class="mh-shell">
    <div class="mh-grid-3">
        @foreach($profiles as $key => $profile)
            <div class="mh-card mh-panel">
                <h5 class="mh-title">{{ $profile['label'] ?? $key }}</h5>
                <div class="mh-subtitle">{{ $profile['description'] ?? '' }}</div>

                @foreach(['required', 'recommended', 'optional'] as $group)
                    <div class="mt-3">
                        <div class="mh-muted small text-uppercase font-weight-bold mb-2">{{ __('module-health::messages.groups.' . $group) }}</div>
                        <div class="mh-pills">
                            @forelse(($profile[$group] ?? []) as $component)
                                <span class="mh-pill">{{ $component }}</span>
                            @empty
                                <span class="mh-muted small">No components configured.</span>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endsection
