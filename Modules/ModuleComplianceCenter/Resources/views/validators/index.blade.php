@extends('layouts.app')

@section('content')
<div >
    <div class="d-flex justify-content-between mb-3">
        <form method="POST" action="{{ route('module_compliance_center.validators.sync') }}" class="js-confirm" data-title="Sync validators?">@csrf<button class="btn btn-outline-primary"><i class="fa-solid fa-rotate me-1"></i>Sync</button></form>
    </div>
    <div class="card shadow-sm"><div class="card-body table-responsive"><table class="table table-striped lsg-datatable"><thead><tr><th>Validator</th><th>Module</th><th>Service</th><th>Available</th><th>Enabled</th><th>Required</th><th>Weight</th><th>Last Checked</th><th>Actions</th></tr></thead><tbody>
        @foreach($validators as $validator)
            <tr><td>{{ $validator->name }}</td><td>{{ $validator->module_name }}</td><td><code>{{ $validator->service_class }}</code></td><td>{{ $validator->is_available ? 'Yes' : 'No' }}</td><td>{{ $validator->is_enabled ? 'Yes' : 'No' }}</td><td>{{ $validator->is_required ? 'Yes' : 'No' }}</td><td>{{ $validator->weight }}</td><td>{{ optional($validator->last_checked_at)->format('Y-m-d H:i') ?? '-' }}</td><td><form method="POST" action="{{ $validator->is_enabled ? route('module_compliance_center.validators.disable', $validator) : route('module_compliance_center.validators.enable', $validator) }}" class="d-inline js-confirm" data-title="Change validator state?">@csrf<button class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-cog"></i></button></form></td></tr>
        @endforeach
    </tbody></table></div></div>
</div>
@endsection

@push('scripts')@include('module-compliance-center::partials.sweetalerts')@endpush

