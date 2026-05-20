@extends('layouts.app')

@section('content')
@php
    $selectedModule = $modules->firstWhere('id', old('managed_module_id', request('module'))) ?? $modules->first();
@endphp
<div >
    <div class="card shadow-sm"><div class="card-body">
        <form method="POST" action="{{ route('module_compliance_center.runs.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label">Module</label>
                    <select name="managed_module_id" id="managed_module_id" class="form-select" required>
                        @foreach($modules as $module)
                            <option value="{{ $module->id }}" data-name="{{ $module->module_name }}" data-path="{{ $module->module_path }}" @selected(optional($selectedModule)->id === $module->id)>
                                {{ $module->module_name }} ({{ $module->module_slug }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label">Module Path</label>
                    <input id="module_path_preview" class="form-control" value="{{ optional($selectedModule)->module_path }}" readonly>
                    <input type="hidden" name="module_name" id="module_name" value="{{ optional($selectedModule)->module_name }}">
                    <input type="hidden" name="module_path" id="module_path" value="{{ optional($selectedModule)->module_path }}">
                </div>
                <div class="col-md-4"><label class="form-label">Source Type</label><input name="source_type" class="form-control" value="{{ old('source_type', 'manual') }}"></div>
                <div class="col-md-4"><label class="form-label">Source ID</label><input name="source_id" class="form-control" value="{{ old('source_id') }}"></div>
                <div class="col-md-4 d-flex align-items-end gap-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="async" value="1"> Async</label><label class="form-check"><input class="form-check-input" type="checkbox" name="generate_report" value="1" checked> Report</label></div>
                <div class="col-12"><label class="form-label">Validators</label><div class="row g-2">@foreach($validators as $validator)<div class="col-md-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="validators[]" value="{{ $validator['key'] }}" checked> {{ $validator['label'] }} @if(!$validator['available'])<span class="text-warning">(unavailable)</span>@endif</label></div>@endforeach</div></div>
            </div>
            <div class="mt-3"><button class="btn btn-outline-primary"><i class="fa-solid fa-play me-1"></i>Run validation</button></div>
        </form>
    </div></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var select = document.getElementById('managed_module_id');
    var nameInput = document.getElementById('module_name');
    var pathInput = document.getElementById('module_path');
    var pathPreview = document.getElementById('module_path_preview');

    function syncModuleFields() {
        var option = select.options[select.selectedIndex];
        nameInput.value = option ? option.dataset.name : '';
        pathInput.value = option ? option.dataset.path : '';
        pathPreview.value = option ? option.dataset.path : '';
    }

    if (select) {
        select.addEventListener('change', syncModuleFields);
        syncModuleFields();
    }
});
</script>
@endpush

