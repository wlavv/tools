@extends('layouts.app')

@section('content')
<div >
    <div class="card shadow-sm">
        <div class="card-body">
            <p class="mb-2"><strong>Refresh seconds:</strong> {{ $config['refresh_seconds'] ?? 30 }}</p>
            <p class="mb-2"><strong>Today limit:</strong> {{ $config['today_limit'] ?? 5 }}</p>
            <p class="mb-0"><strong>Alert limit:</strong> {{ $config['alert_limit'] ?? 8 }}</p>
        </div>
    </div>
</div>
@endsection
