@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0">
            {{ $position->asset->symbol }} — Posição #{{ $position->id }}
        </h1>
        <a href="{{ route('positions.index') }}" class="btn btn-link btn-sm">
            ← Voltar
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body small">
                    <h5 class="card-title h6">Dados da posição</h5>
                    <p class="mb-1"><span class="fw-semibold">Conta:</span> {{ $position->brokerAccount->name }}</p>
                    <p class="mb-1"><span class="fw-semibold">Side:</span> {{ ucfirst($position->side) }}</p>
                    <p class="mb-1"><span class="fw-semibold">Quantidade:</span> {{ $position->quantity }}</p>
                    <p class="mb-1"><span class="fw-semibold">Preço entrada:</span> {{ number_format($position->entry_price, 4) }}</p>
                    <p class="mb-0"><span class="fw-semibold">Estado:</span> {{ $position->status }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body small">
                    <h5 class="card-title h6">Stops e parâmetros</h5>
                    <p class="mb-1"><span class="fw-semibold">Stop Loss atual:</span> {{ number_format($position->current_stop_loss, 4) }}</p>
                    <p class="mb-1"><span class="fw-semibold">Stop Earnings atual:</span> {{ number_format($position->current_stop_earn, 4) }}</p>
                    <p class="mb-1"><span class="fw-semibold">Step:</span> {{ number_format($position->step_value, 4) }}</p>
                    <p class="mb-1"><span class="fw-semibold">Auto-manage:</span> {{ $position->auto_manage ? 'Sim' : 'Não' }}</p>
                    @if($position->pnl !== null)
                        <p class="mb-0"><span class="fw-semibold">PnL:</span> {{ number_format($position->pnl, 2) }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header py-2">
            <span class="small fw-semibold">Histórico de patamares</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Step</th>
                            <th>Stop Loss</th>
                            <th>Stop Earnings</th>
                            <th>Ativado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($position->stopLevels as $level)
                            <tr>
                                <td>{{ $level->step_index }}</td>
                                <td>{{ number_format($level->stop_loss, 4) }}</td>
                                <td>{{ number_format($level->stop_earn, 4) }}</td>
                                <td>{{ $level->activated_at }}</td>
                            </tr>
                        @endforeach
                        @if($position->stopLevels->isEmpty())
                            <tr><td colspan="4" class="text-center text-muted py-3">Sem patamares registados.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-3">
        <div class="card-header py-2">
            <span class="small fw-semibold">Eventos da posição</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Preço</th>
                            <th>Detalhes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($position->events as $event)
                            <tr>
                                <td>{{ $event->event_time }}</td>
                                <td>{{ $event->type }}</td>
                                <td>
                                    @if($event->price) {{ number_format($event->price, 4) }} @endif
                                </td>
                                <td>
                                    <pre class="mb-0 small">{{ json_encode($event->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </td>
                            </tr>
                        @endforeach
                        @if($position->events->isEmpty())
                            <tr><td colspan="4" class="text-center text-muted py-3">Sem eventos ainda.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($position->isOpen())
        <div class="card shadow-sm mb-3">
            <div class="card-header py-2">
                <span class="small fw-semibold">Simular movimento de preço</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('positions.simulateStep', $position) }}" class="row g-2 align-items-end">
                    @csrf
                    <div class="col-auto">
                        <label class="form-label small">Preço atual simulado</label>
                        <input type="number" step="0.0001" name="current_price" class="form-control form-control-sm">
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-primary btn-sm mt-3">
                            Simular
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
