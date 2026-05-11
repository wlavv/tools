<section class="mtg-lsg-panel">
    <div class="mtg-lsg-grid">
        @foreach($sets as $set)
            <a class="mtg-lsg-set-card" href="{{ route('mtg.showSet', [$set->sub_set_code, isset($sub_set) ? 1 : null]) }}">
                <div class="mtg-lsg-set-card__symbol">
                    @if(isset($set->icon_svg_uri))
                        <img src="{{ $set->icon_svg_uri }}" alt="{{ $set->set_name }}">
                    @else
                        <img src="/images/mtg/mana/mtg.png" alt="MTG">
                    @endif
                </div>
                <div>
                    <div class="mtg-lsg-set-card__code">{{ $set->sub_set_code }}</div>
                    <div class="mtg-lsg-set-card__name">{{ $set->set_name }}</div>
                </div>
            </a>
        @endforeach
    </div>
</section>
