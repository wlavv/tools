@extends('layouts.app')

@section('content')
<div class="lsg-content">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h1 class="h3 mb-1">{{ __('module-structure-validator::messages.result_title') }}: {{ $moduleName }}</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">B.O.</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('module_structure_validator.index') }}">Module Structure Validator</a></li>
                    <li class="breadcrumb-item active">Result</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('module_structure_validator.index') }}" class="btn btn-outline-primary">
            <i class="fa fa-angle-left me-1"></i> {{ __('module-structure-validator::messages.back') }}
        </a>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Score</div><div class="h2 mb-0">{{ $result['score'] }}%</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Status</div><span class="badge bg-{{ $result['status'] === 'passed' ? 'success' : ($result['status'] === 'warning' ? 'warning' : 'danger') }}">{{ $result['status'] }}</span></div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Failed</div><div class="h2 mb-0">{{ $result['failed_count'] }}</div></div></div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm"><div class="card-body"><div class="text-muted small">Warnings</div><div class="h2 mb-0">{{ $result['warning_count'] }}</div></div></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle" id="structure-results-table">
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
                        @foreach($result['findings'] as $finding)
                            <tr>
                                <td><span class="badge bg-{{ $finding['status'] === 'passed' ? 'success' : ($finding['status'] === 'warning' ? 'warning' : 'danger') }}">{{ $finding['status'] }}</span></td>
                                <td><span class="badge bg-secondary">{{ $finding['severity'] }}</span></td>
                                <td><code>{{ $finding['code'] }}</code></td>
                                <td>{{ $finding['message'] }}</td>
                                <td><small>{{ $finding['file_path'] }}</small></td>
                                <td>{{ $finding['recommendation'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.jQuery && $.fn.DataTable) {
        $('#structure-results-table').DataTable({ pageLength: 25 });
    }
});
</script>
@endpush
