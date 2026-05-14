@extends('module-health::layouts.module')

@section('content')
@include('module-health::partials.styles')

<div class="mh-shell">
    <div class="mh-card mh-panel">
        <div class="mh-card-head">
            <div>
                <h5 class="mh-title">Module Health Matrix</h5>
                <div class="mh-subtitle">{{ $items->count() }} modules analysed - scan #{{ $scan->id }}</div>
            </div>
            <form method="POST" action="{{ route('module_health.scan.run') }}">
                @csrf
                <button class="btn btn-outline-primary lsg-action-btn lsg-action-btn--primary">
                    <span class="lsg-action-btn__glow"></span>
                    <span class="lsg-action-btn__icon"><i class="fa-solid fa-rotate"></i></span>
                    <span class="lsg-action-btn__label">Run Scan</span>
                </button>
            </form>
        </div>

        @include('module-health::partials.module-table', ['items' => $items])
    </div>
</div>
@endsection
