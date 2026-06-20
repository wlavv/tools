@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">MultiStore</span>
            <h1>Currencies</h1>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>ISO</th>
                    <th>Nome</th>
                    <th>Simbolo</th>
                    <th>Taxa para EUR</th>
                    <th>Ativo</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($currencies as $currency)
                    <tr>
                        <td>{{ $currency->id ?? '-' }}</td>
                        <td><strong>{{ $currency->iso_code ?? '-' }}</strong></td>
                        <td>{{ $currency->name ?? '-' }}</td>
                        <td>{{ $currency->symbol ?? '-' }}</td>
                        <td>{{ isset($currency->conversion_rate_to_eur) ? number_format((float) $currency->conversion_rate_to_eur, 6) : '-' }}</td>
                        <td>{{ !empty($currency->active) ? 'Sim' : 'Nao' }}</td>
                        <td>
                            <a href="{{ route('catalog-manager.currencies.edit', $currency->id) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fa-solid fa-pencil"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
