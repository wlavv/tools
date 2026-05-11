@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-card">
    <h3><i class="{{ $template['icon'] ?? 'fa-solid fa-file-csv' }}"></i> {{ $template['label'] ?? ucfirst($type) }} Import</h3>
    <p class="wc-muted">{{ $template['description'] ?? '' }}</p>
    <div class="wc-actions-row" style="margin-bottom:14px">
        <a class="wc-action-link" href="{{ route('webcatalogue.imports.template', $type) }}"><i class="fa-solid fa-download"></i> Download CSV Template</a>
        <a class="wc-action-link" href="{{ route('webcatalogue.imports.index') }}"><i class="fa-solid fa-angle-left"></i> Import Center</a>
    </div>
    <div class="wc-template-cols" style="margin-bottom:14px">
        <strong>Required:</strong>
        @foreach(($template['required'] ?? []) as $column)<code>{{ $column }}</code>@endforeach
        <br>
        <strong>All columns:</strong>
        @foreach(($template['columns'] ?? []) as $column)<code>{{ $column }}</code>@endforeach
    </div>
    <form method="POST" action="{{ route('webcatalogue.imports.upload', $type) }}" enctype="multipart/form-data">
        @csrf
        <div class="wc-form-grid">
            <div class="wc-field"><label>Store override</label><select name="id_store"><option value="">Detect by CSV store_code</option>@foreach($stores as $store)<option value="{{ $store->id }}">{{ $store->name }}</option>@endforeach</select></div>
            <div class="wc-field"><label>CSV File</label><input type="file" name="csv_file" accept=".csv,.txt"></div>
        </div>
    </form>
</div>
<div class="wc-card" style="margin-top:16px">
    <h3>Recent {{ $template['label'] ?? ucfirst($type) }} batches</h3>
    <table class="wc-table lsg-datatable"><thead><tr><th>ID</th><th>File</th><th>Status</th><th>Date</th></tr></thead><tbody>@foreach($batches as $batch)<tr><td>{{ $batch->id }}</td><td>{{ $batch->filename }}</td><td>{{ $batch->status }}</td><td>{{ $batch->created_at }}</td></tr>@endforeach</tbody></table>
</div>

</div>
@endsection
