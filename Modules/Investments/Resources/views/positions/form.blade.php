@extends(config('investments.layout', 'layouts.app'))

@section('content')
    @include('investments::Includes.css')
    <div class="investments-shell">
        @include('investments::Includes._components.hero', ['title' => 'New Position', 'subtitle' => 'Abre uma posicao com patamares de stop.', 'icon' => 'fa-solid fa-arrow-trend-up'])
        @include('investments::Includes._components.flash')

        <form id="lsg-form" class="investments-card investments-form" method="POST" action="{{ $action }}">
            @csrf
            <div class="investments-field investments-form__full">
                <label>Account</label>
                <select name="broker_account_id" required>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->currency }})</option>
                    @endforeach
                </select>
            </div>
            <div class="investments-field investments-form__full">
                <label>Asset</label>
                <select name="asset_id" required>
                    @foreach($assets as $asset)
                        <option value="{{ $asset->id }}">{{ $asset->symbol }} - {{ $asset->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="investments-field"><label>Side</label><select name="side"><option value="long">Long</option><option value="short">Short</option></select></div>
            <div class="investments-field"><label>Quantity</label><input type="number" step="0.0001" name="quantity" value="{{ old('quantity') }}" required></div>
            <div class="investments-field"><label>Entry Price</label><input type="number" step="0.0001" name="entry_price" value="{{ old('entry_price') }}" required></div>
            <div class="investments-field"><label>Initial Stop Loss</label><input type="number" step="0.0001" name="initial_stop_loss" value="{{ old('initial_stop_loss') }}" required></div>
            <div class="investments-field"><label>Initial Stop Earn</label><input type="number" step="0.0001" name="initial_stop_earn" value="{{ old('initial_stop_earn') }}" required></div>
            <div class="investments-field"><label>Step Value</label><input type="number" step="0.0001" name="step_value" value="{{ old('step_value') }}" required></div>
            <div class="investments-field investments-form__full"><label class="investments-check"><input type="checkbox" name="auto_manage" value="1" checked> Auto-manage stops</label></div>
            <div class="investments-actions investments-form__full">
                <a href="{{ route('investments.positions.index') }}" class="lsg-action-btn lsg-action-btn--back"><i class="fa-solid fa-angle-left"></i><span>Back</span></a>
                <button class="lsg-action-btn lsg-action-btn--success"><i class="fa-solid fa-floppy-disk"></i><span>Save</span></button>
            </div>
        </form>
    </div>
    @include('investments::Includes.js')
@endsection
