@extends('layouts.app')

@section('content')
<div >
    <div class="card shadow-sm"><div class="card-body table-responsive">
        <table class="table table-striped lsg-datatable">
            <thead><tr><th>Module</th><th>Slug</th><th>Version</th><th>Last Score</th><th>Last Status</th><th>Last Checked</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($modules as $module)
                    <tr>
                        <td>{{ $module->module_name }}</td><td>{{ $module->module_slug }}</td><td>{{ $module->module_version ?? '-' }}</td><td>{{ $module->last_score ?? '-' }}</td>
                        <td>@include('module-compliance-center::partials.status-badge', ['status' => $module->last_status ?? 'pending'])</td><td>{{ optional($module->last_checked_at)->format('Y-m-d H:i') ?? '-' }}</td>
                        <td><a class="btn btn-sm btn-outline-primary" href="{{ route('module_compliance_center.modules.show', $module) }}"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div></div>
</div>
@endsection

@push('scripts')@include('module-compliance-center::partials.sweetalerts')@endpush

