@extends(config('mtg.layout', 'layouts.app'))

@section('content')
    @include('mtg::Includes.css')

    <div class="mtg-lsg">
        @include('mtg::Includes._components.hero', [
            'title' => isset($sub_set) ? 'Subsets' : 'Sets',
            'subtitle' => 'Explora as colecoes e entra diretamente na lista de cartas de cada set.',
            'meta' => $sets->count() . ' sets',
        ])

        @include('mtg::Includes._components.sets', ['sets' => $sets, 'sub_set' => $sub_set ?? null])
    </div>
@endsection
