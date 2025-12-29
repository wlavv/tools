@extends('layouts.app')

@section('content')
    <div class="col-lg-4" style="">    
        <div class="navbar navbar-light customPanel" style="padding: 0;">
            <div class="m-4">
                <h1 class="h3 mb-3">Nova Conta de Corretora</h1>

                <div class="">
                    <div class="card-body">
                        <form action="{{ route('broker-accounts.store') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Nome</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Broker</label>
                                    <input type="text" name="broker" class="form-control" value="{{ old('broker', 'ibkr') }}">
                                    @error('broker')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Moeda</label>
                                    <input type="text" name="currency" class="form-control" value="{{ old('currency', 'EUR') }}">
                                    @error('currency')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check">
                                        <input type="checkbox" name="is_demo" value="1" checked
                                            class="form-check-input" id="is_demo">
                                        <label class="form-check-label" for="is_demo">
                                            Conta demo
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flow-root" class="mt-4">
                                <a href="{{ route('broker-accounts.index') }}" class="btn btn-outline-secondary" style="float: left;">Cancelar</a>
                                <button class="btn btn-success" style="float: right;">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
