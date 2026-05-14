@extends('data-import-wizard::layout')

@section('module-content')
    <h1>Preview da importação #{{ $batch->id }}</h1>

    <div class="grid">
        <div class="metric"><div class="value">{{ $batch->total_rows }}</div><div class="muted">Total</div></div>
        <div class="metric"><div class="value">{{ $batch->valid_rows }}</div><div class="muted">Válidas</div></div>
        <div class="metric"><div class="value">{{ $batch->error_rows }}</div><div class="muted">Com erro</div></div>
        <div class="metric"><div class="value">{{ $batch->warning_rows }}</div><div class="muted">Avisos</div></div>
    </div>

    <div class="card">
        <p><strong>Perfil:</strong> {{ $batch->profile_key }}</p>
        <p><strong>Estado:</strong> {{ $batch->status }}</p>
        <p><strong>Ficheiro:</strong> {{ $batch->original_filename }}</p>

        @if ($batch->valid_rows > 0)
            <form method="post" action="{{ route('data_import_wizard.batches.execute', $batch) }}">
                @csrf
                <input type="hidden" name="mode" value="{{ $batch->mode }}">
                <button type="submit" class="btn">Executar importação</button>
            </form>
        @endif
    </div>

    @include('data-import-wizard::batches.partials.rows-table', ['rows' => $rows])
@endsection
