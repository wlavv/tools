@extends(config('package_tracker.layout'))
@section('content')
@include('package-tracker::partials.flash')

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead><tr><th>Code</th><th>Name</th><th>Driver</th><th>Active</th><th>Webhooks</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @foreach($carriers as $carrier)
                <tr>
                    <td><code>{{ $carrier->code }}</code></td>
                    <td>{{ $carrier->name }}</td>
                    <td><small>{{ $carrier->driver }}</small></td>
                    <td>{!! $carrier->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                    <td>{!! $carrier->supports_webhooks ? '<span class="badge bg-primary">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
                    <td class="text-end"><a href="{{ route('package_tracker.carriers.edit', $carrier) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact" title="Edit"><i class="fa-solid fa-pencil"></i></a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $carriers->links() }}</div>
</div>
@endsection
