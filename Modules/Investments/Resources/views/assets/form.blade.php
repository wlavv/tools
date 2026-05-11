@extends(config('investments.layout', 'layouts.app'))

@section('content')
    @include('investments::Includes.css')
    <div class="investments-shell">
        @include('investments::Includes._components.hero', ['title' => 'New Asset', 'subtitle' => 'Regista um novo instrumento.', 'icon' => 'fa-solid fa-layer-group'])
        @include('investments::Includes._components.flash')

        <form id="lsg-form" class="investments-card investments-form" method="POST" action="{{ $action }}">
            @csrf
            @if($method !== 'POST') @method($method) @endif
            <div class="investments-field"><label>Symbol</label><input name="symbol" value="{{ old('symbol', $asset->symbol) }}" required></div>
            <div class="investments-field"><label>Name</label><input name="name" value="{{ old('name', $asset->name) }}" required></div>
            <div class="investments-field"><label>Broker</label><input name="broker" value="{{ old('broker', $asset->broker ?? 'ibkr') }}"></div>
            <div class="investments-field"><label>External ID</label><input name="external_instrument_id" value="{{ old('external_instrument_id', $asset->external_instrument_id) }}"></div>
            <div class="investments-field"><label>Type</label><input name="type" value="{{ old('type', $asset->type ?? 'stock') }}" required></div>
            <div class="investments-field"><label>Exchange</label><input name="exchange" value="{{ old('exchange', $asset->exchange) }}"></div>
            <div class="investments-actions investments-form__full">
                <a href="{{ route('investments.assets.index') }}" class="lsg-action-btn lsg-action-btn--back"><i class="fa-solid fa-angle-left"></i><span>Back</span></a>
                <button class="lsg-action-btn lsg-action-btn--success"><i class="fa-solid fa-floppy-disk"></i><span>Save</span></button>
            </div>
        </form>
    </div>
    @include('investments::Includes.js')
@endsection
