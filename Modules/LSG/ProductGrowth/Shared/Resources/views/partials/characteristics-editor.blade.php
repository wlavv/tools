@php
    $characteristics = collect($characteristics ?? [])
        ->reject(fn($item) => in_array(($item->slug ?? null), ['type_line', 'oracle_text', 'defense', 'abilities'], true))
        ->values();
    $requiredCharacteristics = $characteristics->filter(fn($item) => (bool) ($item->is_required ?? false))->values();
    $optionalCharacteristics = $characteristics->reject(fn($item) => (bool) ($item->is_required ?? false))->values();
    $selectedCharacteristicValues = $selectedCharacteristicValues ?? [];
    $manaValueCharacteristic = $characteristics->first(fn($item) => ($item->slug ?? null) === 'mana_value');
@endphp

<section class="pc-field-panel pc-stage-form-section">
    <div class="pc-stage-form-section__head">
        <i class="fa-solid fa-list-check"></i>
        <strong>Caracteristicas esperadas da categoria</strong>
    </div>

    @if($characteristics->isNotEmpty())
        @foreach([
            ['title' => 'Obrigatorias', 'items' => $requiredCharacteristics, 'empty' => 'Sem caracteristicas obrigatorias nesta categoria.'],
            ['title' => 'Opcionais', 'items' => $optionalCharacteristics, 'empty' => 'Sem caracteristicas opcionais nesta categoria.'],
        ] as $characteristicGroup)
            <div class="pc-characteristic-section">
                <div class="pc-characteristic-section__head">
                    <strong>{{ $characteristicGroup['title'] }}</strong>
                    <small>{{ $characteristicGroup['items']->count() }}</small>
                </div>

                @if($characteristicGroup['items']->isNotEmpty())
                    @foreach($characteristicGroup['items']->groupBy(fn($item) => $item->category_section ?: 'General') as $sectionName => $sectionCharacteristics)
                        <div class="pc-characteristic-subsection">
                            <div class="pc-form-grid pc-characteristic-form-grid">
                                @foreach($sectionCharacteristics as $characteristic)
                                    @php
                                        $storedCharacteristicValue = old('characteristics.' . $characteristic->id, $selectedCharacteristicValues[$characteristic->id] ?? '');
                                        $selectedCharacteristicList = is_array($storedCharacteristicValue)
                                            ? collect($storedCharacteristicValue)->map(fn($item) => (string) $item)->all()
                                            : collect(explode(',', (string) $storedCharacteristicValue))->map(fn($item) => trim($item))->filter()->all();
                                        $freeTextCharacteristicSlugs = ['mana_value', 'power', 'toughness'];
                                        $characteristicOptions = in_array(($characteristic->slug ?? null), $freeTextCharacteristicSlugs, true)
                                            ? collect()
                                            : collect($characteristic->values ?? []);
                                        $knownCharacteristicValues = $characteristicOptions
                                            ->flatMap(fn($option) => [(string) ($option['label'] ?? ''), (string) ($option['value'] ?? '')])
                                            ->filter()
                                            ->unique()
                                            ->values()
                                            ->all();
                                        $customSelectedCharacteristicList = collect($selectedCharacteristicList)
                                            ->reject(fn($item) => in_array((string) $item, $knownCharacteristicValues, true))
                                            ->values()
                                            ->all();
                                        $singleCharacteristicPicker = in_array($characteristic->slug, ['supertypes', 'card_types', 'condition', 'language', 'finish'], true);
                                    @endphp
                                    <div class="pc-form-span-6 pc-characteristic-field {{ $characteristicOptions->isEmpty() ? 'pc-characteristic-field--free' : '' }}">
                                        <label class="pc-label">
                                            {{ $characteristic->name }}
                                            @if($characteristic->unit)
                                                <small>({{ $characteristic->unit }})</small>
                                            @endif
                                        </label>

                                        @if(($characteristic->slug ?? null) === 'mana_cost')
                                            <input
                                                type="hidden"
                                                name="characteristics[{{ $characteristic->id }}]"
                                                value="{{ $storedCharacteristicValue }}"
                                                data-mana-cost-input
                                            >
                                            <div class="pc-mana-cost-builder" data-mana-cost-builder>
                                                <div class="pc-mana-cost-builder__selected" data-mana-cost-selected aria-label="Mana cost selected symbols"></div>
                                                <div class="pc-mana-cost-builder__palette" aria-label="Mana cost symbols">
                                                    @foreach(['0','1','2','3','4','5','6','7','8','9','10','11','12','X','W','U','B','R','G','C'] as $manaSymbol)
                                                        <button type="button" class="pc-mana-symbol" data-mana-symbol="{{ $manaSymbol }}" aria-label="Adicionar {{ $manaSymbol }}">
                                                            @if(in_array($manaSymbol, ['W','U','B','R','G','C'], true))
                                                                <img src="/images/mtg/custom_images/{{ $manaSymbol }}.svg" alt="{{ $manaSymbol }}">
                                                            @else
                                                                <span>{{ $manaSymbol }}</span>
                                                            @endif
                                                        </button>
                                                    @endforeach
                                                    <button type="button" class="pc-icon-action pc-icon-action--danger" title="Remover ultimo simbolo" data-mana-remove>
                                                        <i class="fa-solid fa-delete-left"></i>
                                                    </button>
                                                    <button type="button" class="pc-icon-action" title="Limpar custo" data-mana-clear>
                                                        <i class="fa-solid fa-eraser"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @elseif($characteristicOptions->isNotEmpty())
                                            <input type="hidden" name="characteristics[{{ $characteristic->id }}][]" value="">
                                            <select class="pc-characteristic-picker__select" name="characteristics[{{ $characteristic->id }}][]" multiple hidden data-characteristic-picker-select>
                                                @foreach($characteristicOptions as $option)
                                                    <option value="{{ $option['label'] }}" @selected(in_array((string) $option['label'], $selectedCharacteristicList, true) || in_array((string) $option['value'], $selectedCharacteristicList, true))>
                                                        {{ $option['label'] }}
                                                    </option>
                                                @endforeach
                                                @foreach($customSelectedCharacteristicList as $customOption)
                                                    <option value="{{ $customOption }}" selected>{{ $customOption }}</option>
                                                @endforeach
                                            </select>

                                            <div class="pc-characteristic-picker" data-characteristic-picker data-characteristic-picker-mode="{{ $singleCharacteristicPicker ? 'single' : 'multiple' }}">
                                                <div class="pc-characteristic-picker__column">
                                                    <div class="pc-characteristic-picker__head">
                                                        <span>Disponiveis{{ $singleCharacteristicPicker ? ' - escolher 1' : '' }}</span>
                                                        <small data-characteristic-picker-count="available">0</small>
                                                    </div>
                                                    <div class="pc-characteristic-picker__custom">
                                                        <input class="pc-input pc-characteristic-picker__search" type="search" placeholder="Pesquisar ou adicionar valor" data-characteristic-picker-search data-characteristic-picker-custom-input>
                                                        <button type="button" class="pc-icon-action pc-icon-action--success" title="Adicionar novo valor" data-characteristic-picker-custom-add>
                                                            <i class="fa-solid fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    <div class="pc-characteristic-picker__list" data-characteristic-picker-available>
                                                        @foreach($characteristicOptions as $option)
                                                            @php($isSelectedOption = in_array((string) $option['label'], $selectedCharacteristicList, true) || in_array((string) $option['value'], $selectedCharacteristicList, true))
                                                            @if(!$isSelectedOption)
                                                                <button type="button" class="pc-characteristic-picker__item" data-characteristic-picker-option data-value="{{ $option['label'] }}" data-search="{{ $option['label'] }} {{ $option['value'] ?? '' }} {{ $option['image_alt'] ?? '' }}" title="{{ $option['label'] }}" aria-label="{{ $option['label'] }}">
                                                                    @if(!empty($option['image_url']))
                                                                        <img src="{{ $option['image_url'] }}" alt="{{ $option['image_alt'] ?? $option['label'] }}">
                                                                    @else
                                                                        <span>{{ $option['label'] }}</span>
                                                                    @endif
                                                                </button>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <div class="pc-characteristic-picker__column pc-characteristic-picker__column--selected">
                                                    <div class="pc-characteristic-picker__head">
                                                        <span>Selecionadas</span>
                                                        <small data-characteristic-picker-count="selected">0</small>
                                                    </div>
                                                    <div class="pc-characteristic-picker__list" data-characteristic-picker-selected>
                                                        @foreach($characteristicOptions as $option)
                                                            @php($isSelectedOption = in_array((string) $option['label'], $selectedCharacteristicList, true) || in_array((string) $option['value'], $selectedCharacteristicList, true))
                                                            @if($isSelectedOption)
                                                                <button type="button" class="pc-characteristic-picker__item is-selected" data-characteristic-picker-option data-value="{{ $option['label'] }}" data-search="{{ $option['label'] }} {{ $option['value'] ?? '' }} {{ $option['image_alt'] ?? '' }}" title="{{ $option['label'] }}" aria-label="{{ $option['label'] }}">
                                                                    @if(!empty($option['image_url']))
                                                                        <img src="{{ $option['image_url'] }}" alt="{{ $option['image_alt'] ?? $option['label'] }}">
                                                                    @else
                                                                        <span>{{ $option['label'] }}</span>
                                                                    @endif
                                                                </button>
                                                            @endif
                                                        @endforeach
                                                        @foreach($customSelectedCharacteristicList as $customOption)
                                                            <button type="button" class="pc-characteristic-picker__item is-selected" data-characteristic-picker-option data-custom="1" data-value="{{ $customOption }}">
                                                                <span>{{ $customOption }}</span>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <input
                                                class="pc-input pc-characteristic-free-input"
                                                name="characteristics[{{ $characteristic->id }}]"
                                                value="{{ $storedCharacteristicValue }}"
                                                placeholder="{{ $characteristic->data_type === 'number' ? 'Valor numerico' : 'Valor' }}"
                                                @if(($characteristic->slug ?? null) === 'mana_value') data-mana-value-input="{{ $characteristic->id }}" @endif
                                            >
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @else
                    <span class="text-muted">{{ $characteristicGroup['empty'] }}</span>
                @endif
            </div>
        @endforeach
    @else
        <span class="text-muted">Seleciona e guarda uma categoria no Admin para carregar as caracteristicas esperadas. Campos vazios ficam como caracteristica nao presente no produto.</span>
    @endif
</section>
