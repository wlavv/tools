<section class="mtg-lsg-hero">
    <div class="mtg-lsg-hero__main">
        <div class="mtg-lsg-hero__icon">
            <img src="{{ $icon ?? '/images/mtg/mana/mtg.png' }}" alt="{{ $iconAlt ?? 'MTG' }}">
        </div>
        <div>
            <p class="mtg-lsg-eyebrow">{{ $eyebrow ?? 'Magic the Gathering' }}</p>
            <h1 class="mtg-lsg-title">{{ $title }}</h1>
            @if(!empty($subtitle))
                <p class="mtg-lsg-subtitle">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if(!empty($meta))
        <div class="mtg-lsg-hero__side">
            <div class="mtg-lsg-meta">
                <i class="{{ $metaIcon ?? 'fa-solid fa-layer-group' }}" aria-hidden="true"></i>
                <span>{{ $meta }}</span>
            </div>
            @if(!empty($webCatalogueAction ?? false))
                <button type="button" class="mtg-lsg-hero-action" data-bs-toggle="modal" data-bs-target="#mtgSendWebCatalogueModal">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    <span>Enviar para WebCatalogue</span>
                </button>
            @endif
        </div>
    @endif
</section>
