@php
    $findings = method_exists($result, 'findings') ? $result->findings() : ($result->findings ?? []);
    $score = method_exists($result, 'score') ? $result->score() : ($result->score ?? null);
@endphp

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa-solid fa-list-check me-2"></i>{{ __('module-integration-validator::messages.results') }}</h5>
                <span class="badge bg-primary">{{ __('module-integration-validator::messages.score') }}: {{ $score ?? 'N/A' }}</span>
            </div>
            <div class="card-body">
                @if(count($findings) === 0)
                    <div class="alert alert-info mb-0">{{ __('module-integration-validator::messages.no_findings') }}</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-striped align-middle datatable">
                            <thead>
                                <tr>
                                    <th>{{ __('module-integration-validator::messages.status') }}</th>
                                    <th>{{ __('module-integration-validator::messages.severity') }}</th>
                                    <th>{{ __('module-integration-validator::messages.code') }}</th>
                                    <th>{{ __('module-integration-validator::messages.message') }}</th>
                                    <th>{{ __('module-integration-validator::messages.file') }}</th>
                                    <th>{{ __('module-integration-validator::messages.recommendation') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($findings as $finding)
                                    @php
                                        $status = is_object($finding->status ?? null) && property_exists($finding->status, 'value') ? $finding->status->value : ($finding->status ?? 'unknown');
                                        $severity = is_object($finding->severity ?? null) && property_exists($finding->severity, 'value') ? $finding->severity->value : ($finding->severity ?? 'info');
                                    @endphp
                                    <tr>
                                        <td><span class="badge bg-{{ $status === 'passed' ? 'success' : ($status === 'failed' ? 'danger' : 'warning') }}">{{ $status }}</span></td>
                                        <td><span class="badge bg-secondary">{{ $severity }}</span></td>
                                        <td><code>{{ $finding->code ?? '' }}</code></td>
                                        <td>{{ $finding->message ?? '' }}</td>
                                        <td><small>{{ $finding->filePath ?? $finding->file_path ?? '' }}</small></td>
                                        <td>{{ $finding->recommendation ?? '' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.jQuery && jQuery.fn.DataTable) {
            jQuery('.datatable').DataTable({
                pageLength: 25,
                order: [[0, 'asc']]
            });
        }
    });
</script>
@endpush
