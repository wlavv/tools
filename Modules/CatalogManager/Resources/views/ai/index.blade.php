@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">AI Product Pipeline</span>
            <h1>AI Product Pipeline</h1>
            <p>Geração, revisão e aplicação controlada de conteúdos AI.</p>
        </div>
    </div>

    <div class="catalog-lsg-card">
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead><tr><th>ID</th><th>Produto</th><th>Tipo</th><th>Estado</th><th>Aplicado</th><th>Data</th></tr></thead>
            <tbody>
                @forelse($generations as $generation)
                    <tr>
                        <td>{{ $generation->id }}</td>
                        <td>{{ $generation->product_id ?: '—' }}</td>
                        <td>{{ $generation->type }}</td>
                        <td><span class="catalog-lsg-badge">{{ $generation->status }}</span></td>
                        <td>{{ $generation->applied ? 'Sim' : 'Não' }}</td>
                        <td>{{ $generation->created_at }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">Sem gerações AI.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
