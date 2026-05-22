@extends('idealab::layouts.master')

@section('idealab-content')
@include('idealab::partials.alerts')

<div class="card idealab-card">
    <div class="card-body table-responsive">
        <table class="table table-hover align-middle lsg-datatable" id="idealab-table">
            <thead>
                <tr>
                    <th>Idea</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Score</th>
                    <th>Readiness</th>
                    <th>Updated</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ideas as $idea)
                    <tr>
                        <td>
                            <strong>{{ $idea->title }}</strong><br>
                            <small class="text-muted">{{ str($idea->description_raw)->limit(100) }}</small>
                        </td>
                        <td>{{ $idea->category?->name ?? '—' }}</td>
                        <td><span class="badge bg-secondary idealab-badge">{{ config('idealab.statuses.' . $idea->status, $idea->status) }}</span></td>
                        <td><span class="badge bg-info idealab-badge">{{ config('idealab.priorities.' . $idea->priority, $idea->priority) }}</span></td>
                        <td><strong>{{ $idea->final_score ?? '—' }}</strong></td>
                        <td>{{ $idea->readiness_label }}</td>
                        <td>{{ $idea->updated_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end text-nowrap">
                            <a href="{{ route('idealab.show', $idea) }}" class="btn btn-sm btn-outline-primary" title="Show"><i class="fa-solid fa-eye"></i></a>
                            <a href="{{ route('idealab.edit', $idea) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fa-solid fa-pencil"></i></a>
                            <form method="POST" action="{{ route('idealab.destroy', $idea) }}" class="d-inline js-idealab-delete">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fa-solid fa-inbox me-1"></i> No ideas found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $ideas->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-idealab-delete').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (form.dataset.confirmed === '1' || typeof Swal === 'undefined') {
                return;
            }

            event.preventDefault();
            Swal.fire({
                title: 'Delete idea?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
