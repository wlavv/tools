@extends('data-import-wizard::layout')

@section('module-content')
    <h1>Importação #{{ $batch->id }}</h1>

    <div class="grid">
        <div class="metric"><div class="value">{{ $batch->total_rows }}</div><div class="muted">Total</div></div>
        <div class="metric"><div class="value">{{ $batch->valid_rows }}</div><div class="muted">Válidas</div></div>
        <div class="metric"><div class="value">{{ $batch->error_rows }}</div><div class="muted">Erros</div></div>
        <div class="metric"><div class="value">{{ $batch->warning_rows }}</div><div class="muted">Avisos</div></div>
    </div>

    <div class="card">
        <p><strong>Perfil:</strong> {{ $batch->profile_key }}</p>
        <p><strong>Classe:</strong> <code>{{ $batch->profile_class }}</code></p>
        <p><strong>Estado:</strong> {{ $batch->status }}</p>
        <p><strong>Modo:</strong> {{ $batch->mode }}</p>
        <p><strong>Ficheiro:</strong> {{ $batch->original_filename }}</p>
        <p><strong>Início:</strong> {{ $batch->started_at ?: '-' }}</p>
        <p><strong>Fim:</strong> {{ $batch->finished_at ?: '-' }}</p>
    </div>

    @include('data-import-wizard::batches.partials.rows-table', ['rows' => $rows])
@endsection
