@extends('layouts.app')

@section('content')
@php
    $statusClass = match($resultArray['status'] ?? 'warning') {
        'passed' => 'success',
        'failed' => 'danger',
        'warning' => 'warning',
        default => 'secondary',
    };
@endphp
<div >
    <p class="text-muted mb-3">{{ $resultArray['metadata']['module_name'] ?? '-' }}</p>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Status</div>
                    <span class="badge bg-{{ $statusClass }} fs-6">{{ strtoupper($resultArray['status'] ?? '-') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Score</div>
                    <div class="h3 mb-0">{{ $resultArray['score'] ?? 0 }}%</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Warnings</div>
                    <div class="h3 mb-0">{{ $resultArray['warning_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Failed</div>
                    <div class="h3 mb-0">{{ $resultArray['failed_count'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="h5 mb-0"><i class="fa fa-list-check me-2"></i>Findings</h2>
            <span class="badge bg-secondary">{{ count($resultArray['findings'] ?? []) }}</span>
        </div>
        <div class="card-body table-responsive">
            @if(empty($resultArray['findings']))
                <div class="empty-state text-center text-muted py-5">
                    <i class="fa fa-circle-check fa-2x mb-3"></i>
                    <p class="mb-0">Sem findings para apresentar.</p>
                </div>
            @else
                <table class="table table-striped table-hover align-middle" id="datatable-design-validator">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Severity</th>
                            <th>Code</th>
                            <th>Title</th>
                            <th>File</th>
                            <th>Recommendation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($resultArray['findings'] as $finding)
                            @php
                                $rowClass = match($finding['status']) {
                                    'passed' => 'success',
                                    'failed' => 'danger',
                                    'warning' => 'warning',
                                    default => 'secondary',
                                };
                            @endphp
                            <tr>
                                <td><span class="badge bg-{{ $rowClass }}">{{ $finding['status'] }}</span></td>
                                <td><span class="badge bg-secondary">{{ $finding['severity'] }}</span></td>
                                <td><code>{{ $finding['code'] }}</code></td>
                                <td>
                                    <strong>{{ $finding['title'] }}</strong><br>
                                    <small class="text-muted">{{ $finding['message'] }}</small>
                                </td>
                                <td><small>{{ $finding['file_path'] ?? '-' }}</small></td>
                                <td>{{ $finding['recommendation'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && jQuery.fn.DataTable) {
        jQuery('#datatable-design-validator').DataTable({
            pageLength: 25,
            order: [[0, 'asc']]
        });
    }
});
</script>
@endpush

