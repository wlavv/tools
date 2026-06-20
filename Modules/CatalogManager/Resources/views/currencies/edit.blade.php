@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Currencies</span>
            <h1>Editar</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <form method="POST" action="{{ route('catalog-manager.currencies.update', $currency->id) }}">
            @csrf
            @method('PUT')
            @include('catalogmanager::currencies._form', ['currency' => $currency])
        </form>
    </div>
@endsection
