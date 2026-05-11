@extends('catalogmanager::layouts.module')

@section('catalogmanager-content')
    <div class="catalog-lsg-hero">
        <div>
            <span class="catalog-lsg-eyebrow">Safe PrestaShop Sync</span>
            <h1>Sync PrestaShop</h1>
            <p>Fila de sincronização controlada. Sync destrutivo não é automático.</p>
        </div>
    </div>

    <div class="catalog-lsg-grid">
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Pending</span><strong>{{ $counts['pending'] ?? 0 }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Processing</span><strong>{{ $counts['processing'] ?? 0 }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Failed</span><strong>{{ $counts['failed'] ?? 0 }}</strong></div>
        <div class="catalog-lsg-card catalog-lsg-kpi"><span>Completed</span><strong>{{ $counts['completed'] ?? 0 }}</strong></div>
    </div>

    <div class="catalog-lsg-card">
        <table class="catalog-lsg-table catalog-lsg-datatable">
            <thead>
                <tr><th>ID</th><th>Entidade</th><th>Operação</th><th>Estado</th><th>Erro</th><th>Criado</th></tr>
            </thead>
            <tbody>
                @foreach($queue as $item)
                    <tr>
                        <td>{{ $item->id }}</td>
                        <td>{{ $item->entity_type }} #{{ $item->entity_id }}</td>
                        <td>{{ $item->operation }}</td>
                        <td><span class="catalog-lsg-badge">{{ $item->status }}</span></td>
                        <td>{{ $item->last_error ? \Illuminate\Support\Str::limit($item->last_error, 80) : '—' }}</td>
                        <td>{{ $item->created_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
    </div>
@endsection
