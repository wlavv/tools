@extends('layouts.app')

@section('content')
<div >
    <div class="card shadow-sm mb-3"><div class="card-body"><h2 class="h5">Summary</h2><p>{{ $report->summary }}</p><div>@include('module-compliance-center::partials.status-badge', ['status' => $report->final_status]) <strong class="ms-2">{{ $report->final_score }}%</strong></div></div></div>
    <div class="card shadow-sm mb-3"><div class="card-header"><strong>Recommendations</strong></div><div class="card-body"><ul class="mb-0">@forelse(($report->recommendations ?? []) as $recommendation)<li>{{ $recommendation }}</li>@empty<li>No recommendations.</li>@endforelse</ul></div></div>
    <div class="card shadow-sm"><div class="card-header"><strong>Payload</strong></div><div class="card-body"><pre class="mb-0">{{ json_encode($report->report_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre></div></div>
</div>
@endsection

