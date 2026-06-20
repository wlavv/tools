@php
    $selectedStoreIds = collect(old('store_ids', $selectedStoreIds ?? []))->map(fn($id) => (int) $id)->all();
@endphp

<div class="catalog-lsg-form-group catalog-lsg-form-group--full">
    <label>Lojas associadas</label>
    <div class="catalog-store-select-grid">
        @forelse($stores ?? collect() as $store)
            <label class="catalog-store-select-option">
                <input type="checkbox" name="store_ids[]" value="{{ $store->id }}" @checked(in_array((int) $store->id, $selectedStoreIds, true))>
                <span><i class="fa-solid fa-store"></i></span>
                <strong>{{ $store->name }}</strong>
            </label>
        @empty
            <span class="text-muted">Sem lojas disponiveis.</span>
        @endforelse
    </div>
</div>
