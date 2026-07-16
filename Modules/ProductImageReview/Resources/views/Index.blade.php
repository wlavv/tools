@extends('layouts.app')

@push('styles')
    @include('product-image-review::Includes.css')
@endpush

@section('content')
<div class="pir-page" id="productImageReview" data-products-url="{{ route('product-image-review.products') }}">
    <div class="pir-header mb-3">
        <div>
            <div class="pir-eyebrow">Marketing · ASM</div>
            <h1>Verificação de imagens por marca</h1>
            <p>Seleciona uma marca para rever visualmente todas as referências e respetivas fotografias.</p>
        </div>
        <div class="pir-filter">
            <label for="pirManufacturer">Marca ASM</label>
            <select id="pirManufacturer" class="form-select">
                <option value="">Selecionar uma marca…</option>
                @foreach($manufacturers as $manufacturer)
                    <option value="{{ $manufacturer->id_manufacturer }}">{{ $manufacturer->name }} ({{ number_format($manufacturer->product_count, 0, ',', '.') }})</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="pir-status" id="pirStatus"><i class="fa-solid fa-hand-pointer"></i><span>Seleciona uma marca para começar.</span></div>
    <div class="pir-list" id="pirList" aria-live="polite"></div>
    <div class="pir-loader" id="pirLoader" hidden><span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>A carregar mais 10 produtos…</span></div>
    <div class="pir-sentinel" id="pirSentinel" aria-hidden="true"></div>
</div>
@endsection

@push('scripts')
    @include('product-image-review::Includes.js')
@endpush
