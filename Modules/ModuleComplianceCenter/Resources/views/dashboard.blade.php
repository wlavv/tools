@extends('layouts.app')

@section('content')
<div >
    <div class="row g-3 mb-3">
        <div class="col-md-2">@include('module-compliance-center::partials.score-card', ['label' => 'Modules Managed', 'value' => $modulesCount, 'icon' => 'fa-solid fa-cubes'])</div>
        <div class="col-md-2">@include('module-compliance-center::partials.score-card', ['label' => 'Validators Available', 'value' => $validatorsAvailable, 'icon' => 'fa-solid fa-list-check'])</div>
        <div class="col-md-2">@include('module-compliance-center::partials.score-card', ['label' => 'Last Runs', 'value' => $lastRuns->count(), 'icon' => 'fa-solid fa-clock-rotate-left'])</div>
        <div class="col-md-2">@include('module-compliance-center::partials.score-card', ['label' => 'Average Score', 'value' => $averageScore, 'suffix' => '%', 'icon' => 'fa-solid fa-gauge-high'])</div>
        <div class="col-md-2">@include('module-compliance-center::partials.score-card', ['label' => 'Blockers', 'value' => $blockers->count(), 'icon' => 'fa-solid fa-triangle-exclamation'])</div>
        <div class="col-md-2">@include('module-compliance-center::partials.score-card', ['label' => 'Changes Required', 'value' => $changesRequired, 'icon' => 'fa-solid fa-screwdriver-wrench'])</div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between"><strong>Latest Runs</strong><a href="{{ route('module_compliance_center.runs.index') }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye me-1"></i>Ver</a></div>
                <div class="card-body table-responsive">
                    <table class="table table-striped lsg-datatable">
                        <thead><tr><th>UUID</th><th>Module</th><th>Status</th><th>Score</th><th>Findings</th><th>Actions</th></tr></thead>
                        <tbody>
                            @foreach($lastRuns as $run)
                                <tr><td><code>{{ $run->uuid }}</code></td><td>{{ $run->module_name }}</td><td>@include('module-compliance-center::partials.status-badge', ['status' => $run->final_status ?? $run->status])</td><td>{{ $run->final_score ?? '-' }}</td><td>{{ $run->total_findings }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('module_compliance_center.runs.show', $run) }}"><i class="fa-solid fa-eye"></i></a></td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header"><strong>Structural Modules</strong></div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                    @foreach($structuralModules as $item)
                        <div class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">
                            <div class="min-w-0">
                                <div class="fw-semibold"><i class="fa-solid fa-cube me-2"></i>{{ $item['label'] }}</div>
                                <div class="small text-muted">
                                    @if($item['module'])
                                        @include('module-compliance-center::partials.status-badge', ['status' => $item['module']->last_status ?? 'pending'])
                                        <span class="ms-1">{{ $item['module']->last_score ?? '-' }}%</span>
                                    @else
                                        Not discovered
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-1">
                                @if($item['module_url'])
                                    <a href="{{ $item['module_url'] }}" class="btn btn-sm btn-outline-primary" title="Compliance detail"><i class="fa-solid fa-eye"></i></a>
                                @endif
                                @if($item['tool_url'])
                                    <a href="{{ $item['tool_url'] }}" class="btn btn-sm btn-outline-primary" title="Open module"><i class="fa-solid fa-up-right-from-square"></i></a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

