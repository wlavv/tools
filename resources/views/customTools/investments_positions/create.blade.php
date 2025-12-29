@extends('layouts.app')

@section('content')
    <div class="col-lg-12">    
        <div class="navbar navbar-light customPanel" style="padding: 0;">
            <div class="m-4">
                <h1 class="h3 mb-3">Nova Posição</h1>

                <div>
                    <div class="card-body">
                        <form action="{{ route('positions.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Conta</label>
                                <select name="broker_account_id" class="form-select">
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->currency }})</option>
                                    @endforeach
                                </select>
                                @error('broker_account_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ativo</label>
                                <select name="asset_id" class="form-select">
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}">{{ $asset->symbol }} - {{ $asset->name }}</option>
                                    @endforeach
                                </select>
                                @error('asset_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Side</label>
                                    <select name="side" class="form-select">
                                        <option value="long">Long</option>
                                        <option value="short">Short</option>
                                    </select>
                                    @error('side')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Quantidade</label>
                                    <input type="number" step="0.0001" name="quantity"
                                        class="form-control" value="{{ old('quantity') }}">
                                    @error('quantity')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Preço de entrada</label>
                                    <input type="number" step="0.0001" name="entry_price"
                                        class="form-control" value="{{ old('entry_price') }}">
                                    @error('entry_price')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-4">
                                    <label class="form-label">Stop Loss inicial</label>
                                    <input type="number" step="0.0001" name="initial_stop_loss"
                                        class="form-control" value="{{ old('initial_stop_loss') }}">
                                    @error('initial_stop_loss')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Stop Earnings inicial</label>
                                    <input type="number" step="0.0001" name="initial_stop_earn"
                                        class="form-control" value="{{ old('initial_stop_earn') }}">
                                    @error('initial_stop_earn')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Step (diferença entre patamares)</label>
                                    <input type="number" step="0.0001" name="step_value"
                                        class="form-control" value="{{ old('step_value') }}">
                                    @error('step_value')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-check mt-3">
                                <input type="checkbox" name="auto_manage" value="1" checked
                                    class="form-check-input" id="auto_manage">
                                <label class="form-check-label" for="auto_manage">
                                    Gerir automaticamente stops por patamares
                                </label>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('positions.index') }}" class="btn btn-outline-secondary me-2">
                                    Cancelar
                                </a>
                                <button class="btn btn-success">
                                    Guardar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
