@extends(config('mtg.layout', 'layouts.app'))

@section('content')
    @include('mtg::Includes.css')

    <div class="mtg-lsg">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-warning">{{ $errors->first() }}</div>@endif

        @include('mtg::Includes._components.hero', [
            'icon' => $set->icon_svg_uri ?? '/images/mtg/mana/mtg.png',
            'iconAlt' => $set->set_name,
            'eyebrow' => strtoupper($set->sub_set_code ?? $set->set_code ?? ''),
            'title' => $set->set_name,
            'subtitle' => 'Lista de cartas com filtros por cor e raridade.',
            'meta' => $card_counters->all . ' cartas',
            'metaIcon' => 'fa-solid fa-clone',
            'webCatalogueAction' => true,
        ])

        @include('mtg::Includes._components.filters', ['card_counters' => $card_counters])
        @include('mtg::Includes._components.cards', ['cards' => $cards, 'set' => $set])
    </div>

    <div class="modal fade mtg-webcatalogue-modal" id="mtgSendWebCatalogueModal" tabindex="-1" aria-labelledby="mtgSendWebCatalogueModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="post" action="{{ route('mtg.showSet.send_webcatalogue', $set->sub_set_code) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="mtgSendWebCatalogueModalTitle">Enviar {{ $set->set_name }} para WebCatalogue</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Loja existente</label>
                                <select class="form-select" data-mtg-wc-store-select>
                                    <option value="">Criar/usar dados abaixo</option>
                                    @foreach($webcatalogueStores ?? [] as $store)
                                        <option value="{{ $store->slug }}" data-name="{{ $store->name }}" data-code="{{ $store->code }}" data-domain="{{ $store->domain }}">{{ $store->name }} · {{ $store->slug }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nome da loja</label>
                                <input class="form-control" name="store_name" value="{{ old('store_name', 'TCG-Collectors') }}" required data-mtg-wc-store-name>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug da loja</label>
                                <input class="form-control" name="store_slug" value="{{ old('store_slug', 'tcg-collectors') }}" required data-mtg-wc-store-slug>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Código da loja</label>
                                <input class="form-control" name="store_code" value="{{ old('store_code', 'TCG-COLLECTORS') }}" required data-mtg-wc-store-code>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Domínio da loja</label>
                                <input class="form-control" name="store_domain" value="{{ old('store_domain', 'tcg-collectors.com') }}" data-mtg-wc-store-domain>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nome do catálogo</label>
                                <input class="form-control" name="catalogue_name" value="{{ old('catalogue_name', 'MTG - ' . $set->set_name) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Slug do catálogo</label>
                                <input class="form-control" name="catalogue_slug" value="{{ old('catalogue_slug', 'mtg-' . strtolower($set->sub_set_code)) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descrição do catálogo</label>
                                <textarea class="form-control" name="catalogue_description" rows="2">{{ old('catalogue_description', 'Magic: The Gathering ' . $set->set_name . ' set catalogue for scan and collector intelligence.') }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="include_sealed_products" value="1" checked>
                                    <span class="form-check-label">Incluir produtos base: full set, booster box e booster pack</span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="skip_card_sync" value="1">
                                    <span class="form-check-label">Não sincronizar cartas/imagens MTG antes de importar</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Importar para WebCatalogue</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('mtg::Includes.js')
    <script>
        document.querySelector('[data-mtg-wc-store-select]')?.addEventListener('change', function () {
            const option = this.options[this.selectedIndex];
            if (!option || !option.value) return;
            document.querySelector('[data-mtg-wc-store-slug]').value = option.value || '';
            document.querySelector('[data-mtg-wc-store-name]').value = option.dataset.name || '';
            document.querySelector('[data-mtg-wc-store-code]').value = option.dataset.code || '';
            document.querySelector('[data-mtg-wc-store-domain]').value = option.dataset.domain || '';
        });
    </script>
@endsection
