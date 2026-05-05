@extends('layouts.app')

@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-card">
    <h3>Import Products by CSV</h3>
    <p class="wc-muted">Expected fields: store_code, catalogue_slug, external_id, external_source, reference, sku, ean13, name, slug, short_description, description, brand, category, price, currency, stock, status, image_urls, manual_urls, video_urls, audio_urls, model_3d_url, ar_file_url, tags.</p>
    <form method="POST" action="{{ route('webcatalogue.imports.products.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="wc-form-grid">
            <div class="wc-field"><label>Store</label><select name="id_store"><option value="">Detect by store_code in CSV</option>@foreach($stores as $store)<option value="{{ $store->id }}">{{ $store->name }}</option>@endforeach</select></div>
            <div class="wc-field"><label>CSV File</label><input type="file" name="csv_file" accept=".csv,.txt"></div>
        </div>
        <button class="wc-btn" type="submit"><i class="fa-solid fa-upload"></i> Upload product CSV</button>
    </form>
</div>
<div class="wc-card" style="margin-top:16px"><h3>Recent batches</h3><table class="wc-table"><tbody>@foreach($batches as $batch)<tr><td>{{ $batch->id }}</td><td>{{ $batch->filename }}</td><td>{{ $batch->status }}</td></tr>@endforeach</tbody></table></div>

</div>
@endsection
