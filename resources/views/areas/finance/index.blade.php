@extends('layouts.app')

    @include("areas.finance.includes.js")
    @include("areas.finance.includes.css")

@section('content')

    <div class="col-lg-12" style="">    
        <div class="navbar navbar-light customPanel" style="padding: 0;">
            <div class="m-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h1 class="h3 mb-1">Dashboard de Investimentos</h1>
                        <p class="text-muted small mb-0">
                            Visão geral das tuas posições e estratégia de stops por patamares.
                        </p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="text-muted text-uppercase small mb-1">Posições ativas</div>
                                <div class="h4 mb-0">
                                    {{-- Ex: {{ $activePositionsCount ?? '—' }} --}}
                                    —
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="text-muted text-uppercase small mb-1">PnL diário</div>
                                <div class="h4 mb-0">
                                    {{-- Ex: {{ number_format($dailyPnl, 2) }} € --}}
                                    —
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="text-muted text-uppercase small mb-1">Score médio das entradas</div>
                                <div class="h4 mb-0">
                                    {{-- Ex: {{ $averageScore ?? '—' }} --}}
                                    —
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="small fw-semibold">Atividade recente</span>
                        <a href="{{ route('positions.index') }}" class="small">Ver todas as posições</a>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-0">
                            Aqui podes listar os últimos eventos de posições (stops atingidos, patamares movidos, etc.).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection