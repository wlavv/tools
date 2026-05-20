@extends('layouts.app')

@section('content')
<div >
    <div class="d-flex justify-content-between mb-3">
        <a class="btn btn-outline-primary" href="{{ route('module_compliance_center.runs.create', ['module' => $module->id]) }}"><i class="fa-solid fa-play me-1"></i>Run validation</a>
    </div>
    <div class="card shadow-sm mb-3"><div class="card-body"><div class="row"><div class="col-md-3"><strong>Slug</strong><div>{{ $module->module_slug }}</div></div><div class="col-md-3"><strong>Version</strong><div>{{ $module->module_version ?? '-' }}</div></div><div class="col-md-3"><strong>Last Score</strong><div>{{ $module->last_score ?? '-' }}</div></div><div class="col-md-3"><strong>Status</strong><div>@include('module-compliance-center::partials.status-badge', ['status' => $module->last_status ?? 'pending'])</div></div></div><div class="small text-muted mt-3">{{ $module->module_path }}</div></div></div>
    <div class="card shadow-sm"><div class="card-header"><strong>Run History</strong></div><div class="card-body table-responsive"><table class="table table-striped lsg-datatable"><thead><tr><th>UUID</th><th>Status</th><th>Score</th><th>Findings</th><th>Created</th><th>Actions</th></tr></thead><tbody>@foreach($module->runs as $run)<tr><td><code>{{ $run->uuid }}</code></td><td>@include('module-compliance-center::partials.status-badge', ['status' => $run->final_status ?? $run->status])</td><td>{{ $run->final_score ?? '-' }}</td><td>{{ $run->total_findings }}</td><td>{{ $run->created_at->format('Y-m-d H:i') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('module_compliance_center.runs.show', $run) }}"><i class="fa-solid fa-eye"></i></a></td></tr>@endforeach</tbody></table></div></div>
</div>
@endsection

