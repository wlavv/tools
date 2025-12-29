@extends('layouts.app')

@section('content')
    <div class="col-lg-12">    
        <div class="navbar navbar-light customPanel" style="padding: 0;">
            <div class="m-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h3 mb-0">Posições</h1>
                    <a href="{{ route('positions.create') }}" class="btn btn-success btn-sm"> Nova Posição </a>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                    </div>
                @endif

                <div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle text-center" style="border: 1px solid #aaa">
                            <thead class="table-light">
                                <tr>
                                    <th>Ativo</th>
                                    <th>Conta</th>
                                    <th>Side</th>
                                    <th>Qtd</th>
                                    <th>Entrada</th>
                                    <th>SL Atual</th>
                                    <th>SE Atual</th>
                                    <th>Estado</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($positions as $position)
                                    @php
                                        $isOpen = $position->status === 'open';
                                    @endphp
                                    <tr>
                                        <td>{{ $position->asset->symbol }}</td>
                                        <td>{{ $position->brokerAccount->name }}</td>
                                        <td class="text-capitalize">{{ $position->side }}</td>
                                        <td>{{ $position->quantity }}</td>
                                        <td>{{ number_format($position->entry_price, 2) }}</td>
                                        <td>{{ number_format($position->current_stop_loss, 2) }}</td>
                                        <td>{{ number_format($position->current_stop_earn, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $isOpen ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $position->status }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('positions.show', $position) }}" class="btn btn-link btn-sm p-0 me-2">
                                                Ver
                                            </a>
                                            @if($position->isOpen())
                                                <form action="{{ route('positions.destroy', $position) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-link btn-sm text-danger p-0"
                                                            onclick="return confirm('Fechar posição?')">
                                                        Fechar
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            Sem posições ainda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($positions instanceof \Illuminate\Pagination\AbstractPaginator)
                        <div class="card-footer">
                            {{ $positions->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
