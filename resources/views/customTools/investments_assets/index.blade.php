@extends('layouts.app')
@section('content')
    <div class="col-lg-12">    
        <div class="navbar navbar-light customPanel" style="padding: 0;">
            <div class="m-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Ativos</h1>
                    <a href="{{ route('assets.create') }}" class="btn btn-success btn-sm"> Novo Ativo </a>
                </div>
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif
                <div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 small align-middle text-center" style="border: 1px solid #aaa">
                            <thead class="table-light">
                                <tr>
                                    <th>Símbolo</th>
                                    <th>Nome</th>
                                    <th>Tipo</th>
                                    <th>Bolsa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($assets as $asset)
                                    <tr>
                                        <td>{{ $asset->symbol }}</td>
                                        <td>{{ $asset->name }}</td>
                                        <td>{{ $asset->type }}</td>
                                        <td>{{ $asset->exchange }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3"> Sem ativos ainda. </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($assets instanceof \Illuminate\Pagination\AbstractPaginator)
                        <div class="card-footer"> {{ $assets->links() }} </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection