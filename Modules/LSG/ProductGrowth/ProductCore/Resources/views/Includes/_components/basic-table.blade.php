<div class="product-core-card p-3">
    <div class="product-core-table-wrap">
        <table class="product-core-table">
            <thead><tr>@foreach($columns as $label)<th>{{ $label }}</th>@endforeach<th>Ações</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    @foreach($columns as $field => $label)
                        <td>@if($field==='is_active'){{ $item->$field ? 'Sim' : 'Não' }}@else{{ $item->$field ?? '-' }}@endif</td>
                    @endforeach
                    <td><div class="d-flex gap-1"><a href="{{ route('product_growth.product_core.' . $type . '.edit', $item) }}" class="lsg-action-btn lsg-action-btn--warning lsg-action-btn--compact"><i class="fa-solid fa-pencil"></i></a><form method="POST" data-confirm="Remover/desativar este registo?" action="{{ route('product_growth.product_core.' . $type . '.destroy', $item) }}" class="lsg-action-form">@csrf @method('DELETE')<button class="lsg-action-btn lsg-action-btn--danger lsg-action-btn--compact"><i class="fa-solid fa-trash"></i></button></form></div></td>
                </tr>
            @empty<tr><td colspan="{{ count($columns)+1 }}" class="text-center text-muted py-4">Sem registos.</td></tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $items->links() }}</div>
</div>
