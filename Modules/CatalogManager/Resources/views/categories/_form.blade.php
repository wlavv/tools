@php
    $category = $category ?? null;
    $isCreate = empty($category?->id);
    $characteristics = collect($characteristics ?? []);
    $combinationAttributes = collect($combinationAttributes ?? []);
    $selectedCharacteristicIds = collect(old('characteristic_ids', array_keys($selectedCategoryCharacteristics ?? [])))->map(fn($id) => (int) $id)->all();
    $requiredCharacteristicIds = collect(old(
        'required_characteristic_ids',
        collect($selectedCategoryCharacteristics ?? [])->filter(fn($config) => (bool) ($config['is_required'] ?? false))->keys()->all()
    ))->map(fn($id) => (int) $id)->all();
    $selectedCombinationAttributeIds = collect(old('combination_attribute_ids', array_keys($selectedCategoryCombinationAttributes ?? [])))->map(fn($id) => (int) $id)->all();
    $requiredCombinationAttributeIds = collect(old(
        'required_combination_attribute_ids',
        collect($selectedCategoryCombinationAttributes ?? [])->filter(fn($config) => (bool) ($config['is_required'] ?? false))->keys()->all()
    ))->map(fn($id) => (int) $id)->all();
@endphp

<div class="catalog-category-edit-grid">
    <section class="catalog-category-panel catalog-category-panel--details">
        <div class="catalog-category-panel__head">
            <i class="fa-solid fa-folder-tree"></i>
            <strong>Detalhe da categoria</strong>
        </div>

        <div class="catalog-lsg-form-grid">
            <div class="catalog-lsg-form-group">
                <label>{{ $isCreate ? 'Loja principal' : 'Loja' }}</label>
                <select name="store_id" required>
                    <option value="">-</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" @selected(old('store_id', $category->store_id ?? null) == $store->id)>
                            {{ $store->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($isCreate)
                @include('catalogmanager::partials.store-checkboxes', ['stores' => $stores ?? collect(), 'selectedStoreIds' => old('store_ids', [])])
            @endif

            <div class="catalog-lsg-form-group">
                <label>Categoria Pai</label>
                <select name="parent_id">
                    <option value="">Sem parent</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id ?? null) == $parent->id)>
                            {{ $parent->store_name }} / {{ $parent->name ?: 'Categoria #' . $parent->id }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="catalog-lsg-form-group">
                <label>Nome</label>
                <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required>
            </div>

            <div class="catalog-lsg-form-group">
                <label>Code</label>
                <input type="text" name="code" value="{{ old('code', $category->code ?? '') }}">
            </div>

            <div class="catalog-lsg-form-group">
                <label>Locale</label>
                <input type="text" name="locale" value="{{ old('locale', $category->locale ?? 'pt') }}">
            </div>

            <div class="catalog-lsg-form-group">
                <label>Link rewrite</label>
                <input type="text" name="link_rewrite" value="{{ old('link_rewrite', $category->link_rewrite ?? '') }}">
            </div>

            <div class="catalog-lsg-form-group">
                <label>Meta title</label>
                <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title ?? '') }}">
            </div>

            <div class="catalog-lsg-form-group">
                <label>Posição</label>
                <input type="number" name="position" value="{{ old('position', $category->position ?? 0) }}">
            </div>

            <div class="catalog-lsg-form-group">
                <label><input type="checkbox" name="active" value="1" @checked(old('active', $category->active ?? true))> Ativa</label>
            </div>

            <div class="catalog-lsg-form-group catalog-lsg-form-group--full">
                <label>Descrição</label>
                <textarea name="description" rows="4">{{ old('description', $category->description ?? '') }}</textarea>
            </div>

            <div class="catalog-lsg-form-group catalog-lsg-form-group--full">
                <label>Meta description</label>
                <textarea name="meta_description" rows="3">{{ old('meta_description', $category->meta_description ?? '') }}</textarea>
            </div>

            <div class="catalog-lsg-form-group catalog-lsg-form-group--full">
                <label>Prompt base IA da categoria</label>
                <textarea name="ai_prompt" rows="6" placeholder="Instrucoes base que o Product Growth deve usar ao gerar descricoes para produtos desta categoria.">{{ old('ai_prompt', $category->ai_prompt ?? '') }}</textarea>
                <small>
                    Variaveis:
                    <code>@{{ product.name }}</code>,
                    <code>@{{ product.sku }}</code>,
                    <code>@{{ product.manufacturer }}</code>,
                    <code>@{{ product.characteristic_text }}</code>,
                    <code>@{{ category.name }}</code>,
                    <code>@{{ store.name }}</code>.
                </small>
            </div>
        </div>
    </section>

    <section class="catalog-category-panel catalog-category-panel--rules">
        <div class="catalog-category-panel__head">
            <i class="fa-solid fa-list-check"></i>
            <strong>Características</strong>
            <a href="{{ route('catalog-manager.characteristics.create') }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-plus"></i></a>
        </div>

        @forelse($characteristics as $characteristic)
            @php
                $characteristicId = (int) $characteristic->id;
                $values = collect($characteristic->values ?? []);
                $selectedAllowedValues = old(
                    'characteristic_allowed_values.' . $characteristicId,
                    $selectedCategoryCharacteristics[$characteristicId]['allowed_values'] ?? []
                );
                $selectedAllowedValues = $selectedAllowedValues ?: $values->pluck('value')->all();
            @endphp
            <div class="catalog-category-rule-card is-collapsed" data-catalog-category-rule>
                <div class="catalog-category-rule-card__top" data-catalog-category-rule-toggle>
                    <label class="catalog-category-rule-card__main" onclick="event.stopPropagation()">
                        <input type="checkbox" name="characteristic_ids[]" value="{{ $characteristicId }}" @checked(in_array($characteristicId, $selectedCharacteristicIds, true))>
                        <span>
                            <strong>{{ $characteristic->name }}</strong>
                            <small>{{ $characteristic->slug }}</small>
                        </span>
                    </label>
                    <label class="catalog-category-rule-card__required" onclick="event.stopPropagation()">
                        <input type="checkbox" name="required_characteristic_ids[]" value="{{ $characteristicId }}" @checked(in_array($characteristicId, $requiredCharacteristicIds, true))>
                        Essencial
                    </label>
                    <button type="button" class="catalog-category-rule-card__toggle" aria-label="Expandir valores">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </div>
                @if($values->isNotEmpty())
                    <div class="catalog-category-value-grid">
                        @foreach($values as $value)
                            <label>
                                <input type="checkbox" name="characteristic_allowed_values[{{ $characteristicId }}][]" value="{{ $value['value'] }}" @checked(in_array((string) $value['value'], array_map('strval', $selectedAllowedValues), true))>
                                {{ $value['label'] }}
                            </label>
                        @endforeach
                    </div>
                @else
                    <small class="catalog-category-muted">Sem valores predefinidos.</small>
                @endif
            </div>
        @empty
            <p class="catalog-category-muted">Sem características configuradas.</p>
        @endforelse
    </section>

    <section class="catalog-category-panel catalog-category-panel--rules">
        <div class="catalog-category-panel__head">
            <i class="fa-solid fa-layer-group"></i>
            <strong>Combinações</strong>
            <a href="{{ route('catalog-manager.combination-attributes.create') }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-plus"></i></a>
        </div>

        @forelse($combinationAttributes as $attribute)
            @php
                $attributeId = (int) $attribute->id;
                $values = collect($attribute->values ?? []);
                $selectedAllowedValues = old(
                    'combination_allowed_values.' . $attributeId,
                    $selectedCategoryCombinationAttributes[$attributeId]['allowed_values'] ?? []
                );
                $selectedAllowedValues = $selectedAllowedValues ?: $values->pluck('value')->all();
            @endphp
            <div class="catalog-category-rule-card is-collapsed" data-catalog-category-rule>
                <div class="catalog-category-rule-card__top" data-catalog-category-rule-toggle>
                    <label class="catalog-category-rule-card__main" onclick="event.stopPropagation()">
                        <input type="checkbox" name="combination_attribute_ids[]" value="{{ $attributeId }}" @checked(in_array($attributeId, $selectedCombinationAttributeIds, true))>
                        <span>
                            <strong>{{ $attribute->name }}</strong>
                            <small>{{ $attribute->slug }}</small>
                        </span>
                    </label>
                    <label class="catalog-category-rule-card__required" onclick="event.stopPropagation()">
                        <input type="checkbox" name="required_combination_attribute_ids[]" value="{{ $attributeId }}" @checked(in_array($attributeId, $requiredCombinationAttributeIds, true))>
                        Obrigatório
                    </label>
                    <button type="button" class="catalog-category-rule-card__toggle" aria-label="Expandir valores">
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                </div>
                @if($values->isNotEmpty())
                    <div class="catalog-category-value-grid">
                        @foreach($values as $value)
                            <label>
                                <input type="checkbox" name="combination_allowed_values[{{ $attributeId }}][]" value="{{ $value['value'] }}" @checked(in_array((string) $value['value'], array_map('strval', $selectedAllowedValues), true))>
                                {{ $value['label'] }}
                            </label>
                        @endforeach
                    </div>
                @else
                    <small class="catalog-category-muted">Sem valores predefinidos.</small>
                @endif
            </div>
        @empty
            <p class="catalog-category-muted">Sem atributos de combinação configurados.</p>
        @endforelse
    </section>
</div>
