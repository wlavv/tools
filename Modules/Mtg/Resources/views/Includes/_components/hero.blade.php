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
        <div class="mtg-lsg-meta">
            <i class="{{ $metaIcon ?? 'fa-solid fa-layer-group' }}" aria-hidden="true"></i>
            <span>{{ $meta }}</span>
        </div>
    @endif
</section>
