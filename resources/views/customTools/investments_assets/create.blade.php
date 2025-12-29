@extends('layouts.app')

@section('content')

    <div class="col-lg-4" style="">    
        <div class="navbar navbar-light customPanel" style="padding: 0;">
            <div class="m-4">
                <h1 class="h3 mb-3">Novo Ativo</h1>
                <div style="width: 100%;">
                    <form action="{{ route('assets.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Símbolo</label>
                            <input type="text" name="symbol" class="form-control" value="{{ old('symbol') }}">
                            @error('symbol') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}">
                            @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo</label>
                                <input type="text" name="type" class="form-control" value="{{ old('type', 'stock') }}">
                                @error('type') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bolsa</label>
                                <input type="text" name="exchange" class="form-control" value="{{ old('exchange') }}">
                                @error('exchange') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary me-2"> Cancelar </a>
                            <button class="btn btn-success">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
