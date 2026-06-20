@php
    $characteristic = $characteristic ?? null;
    $valuesRows = collect(old('values', $valuesRows ?? []))
        ->filter(fn($row) => is_array($row) || is_object($row))
        ->map(fn($row) => is_object($row) ? (array) $row : $row)
        ->values();
    if ($valuesRows->isEmpty()) {
        $valuesRows = collect([[
            'id' => '',
            'label' => '',
            'value' => '',
            'image_url' => '',
            'image_alt' => '',
            'position' => 1,
            'active' => true,
        ]]);
    }
@endphp

<div class="catalog-lsg-form-grid">
    <div class="catalog-lsg-form-group">
        <label>Nome</label>
        <input type="text" name="name" value="{{ old('name', $characteristic->name ?? '') }}" required>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Slug</label>
        <input type="text" name="slug" value="{{ old('slug', $characteristic->slug ?? '') }}" placeholder="automatico se vazio">
    </div>

    <div class="catalog-lsg-form-group">
        <label>Tipo</label>
        @php($selectedType = old('data_type', $characteristic->data_type ?? 'select'))
        <select name="data_type" required>
            @foreach(['select' => 'Select', 'text' => 'Texto', 'number' => 'Numero', 'boolean' => 'Sim/Nao'] as $value => $label)
                <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Uso</label>
        @php($selectedUsageScope = old('usage_scope', $characteristic->usage_scope ?? 'product'))
        <select name="usage_scope" required>
            @foreach(['product' => 'Caracteristica do produto', 'combination' => 'Atributo de combinacao', 'both' => 'Produto e combinacao'] as $value => $label)
                <option value="{{ $value }}" @selected($selectedUsageScope === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div class="catalog-lsg-form-group">
        <label>Unidade</label>
        <input type="text" name="unit" value="{{ old('unit', $characteristic->unit ?? '') }}" placeholder="ex: cm, kg, EUR">
    </div>

    <div class="catalog-lsg-form-group catalog-lsg-form-group--full">
        <label>Valores selecionaveis</label>
        <div class="catalog-characteristic-values" data-characteristic-values>
            <div class="catalog-characteristic-values__head">
                <span>Nome</span>
                <span>Codigo</span>
                <span>Imagem / simbolo</span>
                <span>Alt</span>
                <span>Pos.</span>
                <span>Ativo</span>
                <span></span>
            </div>
            <div class="catalog-characteristic-values__list" data-characteristic-values-list>
                @foreach($valuesRows as $index => $row)
                    <div class="catalog-characteristic-values__row" data-characteristic-value-row>
                        <input type="hidden" name="values[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
                        <input type="text" name="values[{{ $index }}][label]" value="{{ $row['label'] ?? '' }}" placeholder="Ex: White">
                        <input type="text" name="values[{{ $index }}][value]" value="{{ $row['value'] ?? '' }}" placeholder="auto">
                        <div class="catalog-characteristic-values__image">
                            <input type="hidden" name="values[{{ $index }}][image_url]" value="{{ $row['image_url'] ?? '' }}">
                            @if(!empty($row['image_url']))
                                <img src="{{ $row['image_url'] }}" alt="{{ $row['image_alt'] ?? $row['label'] ?? 'Imagem' }}">
                            @else
                                <span></span>
                            @endif
                            <input type="file" name="values[{{ $index }}][image_upload]" accept="image/*">
                        </div>
                        <input type="text" name="values[{{ $index }}][image_alt]" value="{{ $row['image_alt'] ?? '' }}" placeholder="Alt">
                        <input type="number" name="values[{{ $index }}][position]" value="{{ $row['position'] ?? ($index + 1) }}" min="0" step="1">
                        <label class="catalog-characteristic-values__active">
                            <input type="hidden" name="values[{{ $index }}][active]" value="0">
                            <input type="checkbox" name="values[{{ $index }}][active]" value="1" @checked((bool) ($row['active'] ?? true))>
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-characteristic-value-remove title="Remover valor">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-outline-success" data-characteristic-value-add>
                <i class="fa-solid fa-plus"></i> Adicionar valor
            </button>
        </div>
        <small>Quando existirem valores, o Product Growth apresenta esta caracteristica como select no produto.</small>
    </div>

    <div class="catalog-lsg-form-group">
        <label><input type="checkbox" name="is_filterable" value="1" @checked(old('is_filterable', $characteristic->is_filterable ?? true))> Usar em filtros</label>
    </div>

    <div class="catalog-lsg-form-group">
        <label><input type="checkbox" name="is_searchable" value="1" @checked(old('is_searchable', $characteristic->is_searchable ?? true))> Usar em pesquisa</label>
    </div>

    <div class="catalog-lsg-form-group">
        <label><input type="checkbox" name="is_seo_keyword" value="1" @checked(old('is_seo_keyword', $characteristic->is_seo_keyword ?? true))> Usar em SEO</label>
    </div>

    <div class="catalog-lsg-form-group">
        <label><input type="checkbox" name="is_syncable" value="1" @checked(old('is_syncable', $characteristic->is_syncable ?? true))> Sincronizavel</label>
    </div>

    <div class="catalog-lsg-form-group">
        <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $characteristic->is_active ?? true))> Ativa</label>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('[data-characteristic-values]').forEach(function(wrapper){
    const list = wrapper.querySelector('[data-characteristic-values-list]');
    const add = wrapper.querySelector('[data-characteristic-value-add]');
    if (!list || !add) {
        return;
    }

    function nextIndex() {
        return list.querySelectorAll('[data-characteristic-value-row]').length;
    }

    add.addEventListener('click', function(){
        const index = nextIndex();
        const row = document.createElement('div');
        row.className = 'catalog-characteristic-values__row';
        row.setAttribute('data-characteristic-value-row', '');
        row.innerHTML = '<input type="hidden" name="values[' + index + '][id]" value=""><input type="text" name="values[' + index + '][label]" value="" placeholder="Ex: White"><input type="text" name="values[' + index + '][value]" value="" placeholder="auto"><div class="catalog-characteristic-values__image"><input type="hidden" name="values[' + index + '][image_url]" value=""><span></span><input type="file" name="values[' + index + '][image_upload]" accept="image/*"></div><input type="text" name="values[' + index + '][image_alt]" value="" placeholder="Alt"><input type="number" name="values[' + index + '][position]" value="' + (index + 1) + '" min="0" step="1"><label class="catalog-characteristic-values__active"><input type="hidden" name="values[' + index + '][active]" value="0"><input type="checkbox" name="values[' + index + '][active]" value="1" checked></label><button type="button" class="btn btn-sm btn-outline-danger" data-characteristic-value-remove title="Remover valor"><i class="fa-solid fa-xmark"></i></button>';
        list.appendChild(row);
    });

    wrapper.addEventListener('click', function(event){
        const button = event.target.closest('[data-characteristic-value-remove]');
        if (!button) {
            return;
        }

        const rows = list.querySelectorAll('[data-characteristic-value-row]');
        const row = button.closest('[data-characteristic-value-row]');
        if (rows.length === 1) {
            row.querySelectorAll('input').forEach(function(input){
                if (input.type === 'checkbox') {
                    input.checked = true;
                } else if (input.type !== 'hidden') {
                    input.value = '';
                }
            });

            return;
        }

        row?.remove();
    });
});
</script>
@endpush
