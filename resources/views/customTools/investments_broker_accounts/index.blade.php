@extends('layouts.app')

@section('content')

    <div class="col-lg-12">    
        <div class="navbar navbar-light customPanel" style="padding: 0;">
            <div class="m-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Contas de Corretora</h1>
                    <a href="{{ route('broker-accounts.create') }}" class="btn btn-success btn-sm">
                        Nova Conta
                    </a>
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
                                    <th>Nome</th>
                                    <th>Broker</th>
                                    <th>Moeda</th>
                                    <th>Tipo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $acc)
                                    <tr>
                                        <td>{{ $acc->name }}</td>
                                        <td>{{ $acc->broker }}</td>
                                        <td>{{ $acc->currency }}</td>
                                        <td>
                                            <span class="badge {{ $acc->is_demo ? 'bg-primary' : 'bg-success' }}">
                                                {{ $acc->is_demo ? 'Demo' : 'Live' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">
                                            Sem contas ainda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($accounts instanceof \Illuminate\Pagination\AbstractPaginator)
                        <div class="card-footer">
                            {{ $accounts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
