@php
    $filters = [
        ['key' => 'all', 'image' => '/images/mtg/mana/mtg.png', 'count' => $card_counters->all],
        ['key' => '1', 'image' => '/images/mtg/mana/white.svg', 'count' => $card_counters->white],
        ['key' => '2', 'image' => '/images/mtg/mana/blue.svg', 'count' => $card_counters->blue],
        ['key' => '3', 'image' => '/images/mtg/mana/black.svg', 'count' => $card_counters->black],
        ['key' => '4', 'image' => '/images/mtg/mana/red.svg', 'count' => $card_counters->red],
        ['key' => '5', 'image' => '/images/mtg/mana/green.svg', 'count' => $card_counters->green],
        ['key' => '7', 'image' => '/images/mtg/mana/colorless.svg', 'count' => $card_counters->colorless],
        ['key' => '6', 'image' => '/images/mtg/mana/multicolor.webp', 'count' => $card_counters->multicolor],
    ];
@endphp

<section class="mtg-lsg-panel">
    <div class="mtg-lsg-filters">
        @foreach($filters as $filter)
            <button type="button" class="mtg-lsg-filter {{ $filter['key'] === 'all' ? 'is-active' : '' }}" data-mtg-card-filter="{{ $filter['key'] }}" title="Filtrar">
                <span class="mtg-lsg-filter__icon">
                    <img src="{{ $filter['image'] }}" alt="">
                </span>
                <span class="mtg-lsg-filter__count">{{ $filter['count'] }}</span>
            </button>
        @endforeach
    </div>
</section>
