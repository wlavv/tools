@extends('layouts.app')

@section('content')
<div >
    <div class="row g-3 mb-3">
        <div class="col-md-3">@include('module-compliance-center::partials.score-card', ['label' => 'Final Score', 'value' => $run->final_score ?? 0, 'suffix' => '%', 'icon' => 'fa-solid fa-gauge-high'])</div>
        <div class="col-md-3">@include('module-compliance-center::partials.score-card', ['label' => 'Findings', 'value' => $run->total_findings, 'icon' => 'fa-solid fa-list-check'])</div>
        <div class="col-md-3">@include('module-compliance-center::partials.score-card', ['label' => 'Warnings', 'value' => $run->warning_findings, 'icon' => 'fa-solid fa-circle-exclamation'])</div>
        <div class="col-md-3">@include('module-compliance-center::partials.score-card', ['label' => 'Blockers', 'value' => $run->blocker_findings, 'icon' => 'fa-solid fa-triangle-exclamation'])</div>
    </div>
    <div class="card shadow-sm mb-3"><div class="card-body d-flex flex-wrap gap-2 align-items-center">
        <span>Status: @include('module-compliance-center::partials.status-badge', ['status' => $run->final_status ?? $run->status])</span>
        <form method="POST" action="{{ route('module_compliance_center.runs.approve', $run) }}" class="js-confirm" data-title="Approve run?">@csrf<button class="btn btn-outline-success"><i class="fa-solid fa-check me-1"></i>Approve</button></form>
        <form method="POST" action="{{ route('module_compliance_center.runs.reject', $run) }}" class="js-confirm" data-title="Reject run?">@csrf<input type="hidden" name="reason" value="Rejected from Compliance Center"><button class="btn btn-outline-danger"><i class="fa-solid fa-times me-1"></i>Reject</button></form>
        <form method="POST" action="{{ route('module_compliance_center.runs.request_changes', $run) }}" class="js-confirm" data-title="Request changes?">@csrf<input type="hidden" name="reason" value="Changes requested from Compliance Center"><button class="btn btn-outline-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>Request changes</button></form>
        <form method="POST" action="{{ route('module_compliance_center.runs.send_to_ai', $run) }}" class="js-confirm" data-title="Send to AI Consensus?">@csrf<button class="btn btn-outline-primary"><i class="fa-solid fa-robot me-1"></i>Send to AI</button></form>
        <form method="POST" action="{{ route('module_compliance_center.runs.create_project_tasks', $run) }}" class="js-confirm" data-title="Create project task payloads?">@csrf<button class="btn btn-outline-primary"><i class="fa-solid fa-list-check me-1"></i>Create tasks</button></form>
    </div></div>
    <div class="card shadow-sm mb-3"><div class="card-header"><strong>Validator Scores</strong></div><div class="card-body table-responsive"><table class="table table-striped lsg-datatable"><thead><tr><th>Validator</th><th>Status</th><th>Score</th><th>Findings</th><th>Errors</th><th>Warnings</th><th>Blockers</th></tr></thead><tbody>@foreach($run->validators as $validator)<tr><td>{{ $validator->validator_name }}</td><td>@include('module-compliance-center::partials.status-badge', ['status' => $validator->status])</td><td>{{ $validator->score ?? '-' }}</td><td>{{ $validator->findings_count }}</td><td>{{ $validator->failed_count }}</td><td>{{ $validator->warning_count }}</td><td>{{ $validator->blocker_count }}</td></tr>@endforeach</tbody></table></div></div>
    <div class="card shadow-sm"><div class="card-header"><strong>Findings</strong></div><div class="card-body">@include('module-compliance-center::partials.findings-table', ['findings' => $run->results])</div></div>
</div>
@endsection

@push('scripts')@include('module-compliance-center::partials.sweetalerts')@endpush

