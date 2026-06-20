@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    @php $currency = $currency ?? null; @endphp
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Currencies</span>
            <h1>Criar</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <form method="POST" action="{{ route('catalog-manager.currencies.store') }}">
            @csrf
            @include('catalogmanager::currencies._form', ['currency' => $currency])
        </form>
    </div>
@endsection
