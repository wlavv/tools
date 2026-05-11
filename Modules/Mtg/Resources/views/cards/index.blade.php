@extends(config('mtg.layout', 'layouts.app'))

@section('content')
    @include('mtg::Includes.css')

    <div class="mtg-lsg">
        @include('mtg::Includes._components.hero', [
            'icon' => $set->icon_svg_uri ?? '/images/mtg/mana/mtg.png',
            'iconAlt' => $set->set_name,
            'eyebrow' => strtoupper($set->sub_set_code ?? $set->set_code ?? ''),
            'title' => $set->set_name,
            'subtitle' => 'Lista de cartas com filtros por cor e raridade.',
            'meta' => $card_counters->all . ' cartas',
            'metaIcon' => 'fa-solid fa-clone',
        ])

        @include('mtg::Includes._components.filters', ['card_counters' => $card_counters])
        @include('mtg::Includes._components.cards', ['cards' => $cards, 'set' => $set])
    </div>

    @include('mtg::Includes.js')
@endsection
