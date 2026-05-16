@extends(config('mtg.layout', 'layouts.app'))

@section('content')
@include('mtg::Includes.css')
<div class="mtg-lsg">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-warning">{{ $errors->first() }}</div>@endif

    @include('mtg::Includes._components.hero', [
        'icon' => '/images/mtg/mana/mtg.png',
        'title' => 'TCG-Collectors',
        'subtitle' => 'Ferramenta interna para importar sets MTG para o WebCatalogue através de payload normalizado.',
        'meta' => ['MTG module', 'WebCatalogue import', 'Manual per set'],
    ])

    <section class="mtg-lsg-panel">
        <form method="get" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
            <div style="flex:1;min-width:240px">
                <label>Pesquisar set</label>
                <input name="q" value="{{ $search }}" placeholder="Mirrodin, mrd, expansion..." style="width:100%;height:42px;border:1px solid #d1d5db;border-radius:6px;padding:0 12px">
            </div>
            <button class="btn btn-primary" type="submit">Pesquisar</button>
            <a class="btn btn-secondary" href="{{ route('mtg.tcg_collectors.index', ['refresh' => 1]) }}">Atualizar lista</a>
        </form>
    </section>

    <section class="mtg-lsg-panel">
        <div class="mtg-lsg-grid">
            @foreach($sets as $set)
                @php
                    $code = strtolower($set['code']);
                    $importInfo = $imported[$code] ?? null;
                @endphp
                <div class="mtg-lsg-set-card" style="display:block">
                    <div class="mtg-lsg-set-card__symbol">
                        @if(!empty($set['icon_svg_uri']))
                            <img src="{{ $set['icon_svg_uri'] }}" alt="{{ $set['name'] }}">
                        @else
                            <img src="/images/mtg/mana/mtg.png" alt="MTG">
                        @endif
                    </div>
                    <div class="mtg-lsg-set-card__body">
                        <div class="mtg-lsg-set-card__code">{{ strtoupper($set['code']) }}</div>
                        <div class="mtg-lsg-set-card__name">{{ $set['name'] }}</div>
                        <p style="margin:8px 0;color:#6b7280">{{ $set['set_type'] ?: 'set' }} · {{ $set['released_at'] ?: 'sem data' }}</p>
                        <p style="margin:0 0 12px">
                            @if($importInfo)
                                Importado: {{ $importInfo['products_count'] }} produtos
                            @else
                                Não importado: {{ $set['card_count'] }} cartas
                            @endif
                        </p>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            @if($importInfo)
                                <a class="btn btn-secondary btn-sm" href="{{ route('webcatalogue.catalogues.show', $importInfo['catalogue_id']) }}">Catálogo</a>
                            @endif
                            <form method="post" action="{{ route('mtg.tcg_collectors.import', $code) }}">
                                @csrf
                                <button class="btn btn-primary btn-sm" type="submit">{{ $importInfo ? 'Atualizar' : 'Importar' }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
