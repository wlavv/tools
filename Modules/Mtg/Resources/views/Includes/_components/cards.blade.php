<section class="mtg-lsg-panel">
    <div class="mtg-lsg-grid mtg-lsg-cards">
        @foreach($cards as $card)
            @php
                $cardImage = $card->image_url ?: '/images/mtg/' . $card->set_code . '/' . $card->collector_number . '.jpg';
            @endphp
            <button
                type="button"
                class="mtg-lsg-card"
                data-mtg-card="{{ $card->color_group }}"
                data-mtg-card-open
                data-name="{{ e($card->name) }}"
                data-image="{{ $cardImage }}"
                data-rarity="{{ e($card->rarity) }}"
                data-number="{{ e($card->collector_number) }}"
                data-type="{{ e($card->card_type ?? '') }}"
                data-mana="{{ e($card->mana_cost ?? '') }}"
                data-text="{{ e($card->oracle_text ?? '') }}"
            >
                <div class="mtg-lsg-card__image">
                    <img src="{{ $cardImage }}" alt="{{ $card->name }}">
                </div>
                <div class="mtg-lsg-card__body">
                    <div class="mtg-lsg-card__name">{{ $card->name }}</div>
                    <div class="mtg-lsg-card__footer mtg-rarity-{{ $card->rarity }}">
                        <span>{{ $card->rarity }}</span>
                        @if(isset($set->icon_svg_uri))
                            <img src="{{ $set->icon_svg_uri }}" alt="">
                        @endif
                        <span class="mtg-lsg-card__number">{{ $card->collector_number }}</span>
                    </div>
                </div>
            </button>
        @endforeach
    </div>
</section>

<div class="modal fade mtg-card-modal" id="mtgCardModal" tabindex="-1" role="dialog" aria-labelledby="mtgCardModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <button type="button" class="mtg-card-modal__close" data-dismiss="modal" aria-label="Fechar">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
            <div class="mtg-card-modal__body">
                <div class="mtg-card-modal__image-wrap">
                    <img src="" alt="" class="mtg-card-modal__image" data-mtg-modal-image>
                </div>
                <div class="mtg-card-modal__content">
                    <p class="mtg-lsg-eyebrow" data-mtg-modal-meta></p>
                    <h2 id="mtgCardModalTitle" class="mtg-card-modal__title" data-mtg-modal-title></h2>
                    <div class="mtg-card-modal__chips">
                        <span data-mtg-modal-rarity class="mtg-card-modal__rarity"></span>
                        <span data-mtg-modal-mana></span>
                    </div>
                    <div class="mtg-card-modal__section" data-mtg-modal-type-wrap>
                        <strong>Tipo</strong>
                        <p data-mtg-modal-type></p>
                    </div>
                    <div class="mtg-card-modal__section" data-mtg-modal-text-wrap>
                        <strong>Texto</strong>
                        <p data-mtg-modal-text></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
