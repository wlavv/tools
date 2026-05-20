@extends(config('module-compliance.layout', 'layouts.app'))

@section('content')
<div>
    <div class="mb-3">
        <div class="text-muted">Validation control center for LSG modules.</div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Status</div>
                    <strong>Draft entry point</strong>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Scope</div>
                    <strong>LSG modules</strong>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Validation</div>
                    <strong>Structure + design</strong>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted small">Automation</div>
                    <strong>Manual review first</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-white">
            <strong>Test entry point</strong>
        </div>
        <div class="card-body">
            <p class="mb-2">
                This module is registered and reachable. The next implementation step is to add the compliance runs,
                standards, topics and reports tables before enabling real validation actions.
            </p>
            <div class="alert alert-warning mb-0">
                Generated module code is not executed automatically. Use this screen to validate routing, provider loading,
                views and menu registration before adding persistence.
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <strong>Compliance shortcuts</strong>
            <a href="{{ route('module-compliance.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-eye"></i> View
            </a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped lsg-datatable">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Structure</td>
                        <td><span class="badge bg-secondary">Pending</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-cog"></i></button>
                        </td>
                    </tr>
                    <tr>
                        <td>Reports</td>
                        <td><span class="badge bg-secondary">Pending</span></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-warning"><i class="fa-solid fa-triangle-exclamation"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger js-confirm-delete"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="text-muted small mt-2">
                <i class="fa-solid fa-inbox me-1"></i> Empty state: no records are stored in this draft module yet.
            </div>
            <button type="button" class="btn btn-outline-primary mt-2">
                <i class="fa-solid fa-floppy-disk"></i> Save
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-confirm-delete').forEach(function (button) {
        button.addEventListener('click', function () {
            if (typeof Swal === 'undefined') {
                return;
            }

            Swal.fire({
                title: 'Confirm action?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Confirm',
                cancelButtonText: 'Cancel'
            });
        });
    });
});
</script>
@endpush
