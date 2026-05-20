@extends('layouts.app')

@section('content')
@php
    $status = $resultArray['status'] ?? 'unknown';
    $badge = match($status) {
        'passed' => 'success',
        'failed' => 'danger',
        'warning' => 'warning',
        default => 'secondary',
    };
@endphp
    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Status</div>
                <span class="badge bg-{{ $badge }} fs-6">{{ strtoupper($status) }}</span>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Score</div>
                <div class="h4 mb-0">{{ $resultArray['score'] ?? 0 }}%</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Validator</div>
                <div>{{ $resultArray['validator_label'] ?? 'Security' }}</div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-muted small">Scanned Files</div>
                <div>{{ $resultArray['metadata']['scanned_files'] ?? 0 }}</div>
            </div></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Findings</strong>
            <span class="badge bg-secondary">{{ count($resultArray['findings'] ?? []) }}</span>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped table-hover align-middle datatable">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Severity</th>
                        <th>Code</th>
                        <th>Message</th>
                        <th>File</th>
                        <th>Recommendation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(($resultArray['findings'] ?? []) as $finding)
                        @php
                            $fStatus = $finding['status'] ?? 'unknown';
                            $fBadge = match($fStatus) {
                                'passed' => 'success',
                                'failed' => 'danger',
                                'warning' => 'warning',
                                'skipped' => 'secondary',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td><span class="badge bg-{{ $fBadge }}">{{ $fStatus }}</span></td>
                            <td><span class="badge bg-light text-dark border">{{ $finding['severity'] ?? '-' }}</span></td>
                            <td><code>{{ $finding['code'] ?? '-' }}</code></td>
                            <td>
                                <strong>{{ $finding['title'] ?? '' }}</strong><br>
                                <span class="text-muted small">{{ $finding['message'] ?? '' }}</span>
                            </td>
                            <td><code class="small">{{ $finding['file_path'] ?? '-' }}</code></td>
                            <td>{{ $finding['recommendation'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted">No findings returned.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && $.fn.DataTable) {
            $('.datatable').DataTable();
        }
    });
</script>
@endpush
