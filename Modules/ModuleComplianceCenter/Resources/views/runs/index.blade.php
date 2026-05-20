@extends('layouts.app')

@section('content')
<div >
    <div class="card shadow-sm"><div class="card-body table-responsive"><table class="table table-striped lsg-datatable"><thead><tr><th>UUID</th><th>Module</th><th>Source</th><th>Status</th><th>Final Score</th><th>Findings</th><th>Blockers</th><th>Requested By</th><th>Created At</th><th>Actions</th></tr></thead><tbody>
        @foreach($runs as $run)
            <tr><td><code>{{ $run->uuid }}</code></td><td>{{ $run->module_name }}</td><td>{{ $run->source_type ?? '-' }}</td><td>@include('module-compliance-center::partials.status-badge', ['status' => $run->final_status ?? $run->status])</td><td>{{ $run->final_score ?? '-' }}</td><td>{{ $run->total_findings }}</td><td>{{ $run->blocker_findings }}</td><td>{{ $run->requested_by ?? '-' }}</td><td>{{ $run->created_at->format('Y-m-d H:i') }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('module_compliance_center.runs.show', $run) }}"><i class="fa-solid fa-eye"></i></a> @if($run->report)<a class="btn btn-sm btn-outline-primary" href="{{ route('module_compliance_center.reports.show', $run) }}"><i class="fa-solid fa-file-lines"></i></a>@endif</td></tr>
        @endforeach
    </tbody></table></div></div>
</div>
@endsection

