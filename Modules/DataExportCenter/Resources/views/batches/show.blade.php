@extends('data-export-center::layout')

@section('module-content')
    <h1>Export {{ $batch->uuid }}</h1>

    <div class="panel">
        <table>
            <tr><th>Status</th><td>{{ $batch->status }}</td></tr>
            <tr><th>Perfil</th><td>{{ $batch->profile_key }}</td></tr>
            <tr><th>Formato</th><td>{{ strtoupper($batch->format) }}</td></tr>
            <tr><th>Linhas</th><td>{{ $batch->rows_count }}</td></tr>
            <tr><th>Ficheiro</th><td>{{ $batch->path }}</td></tr>
            <tr><th>Erros</th><td>{{ implode('; ', $batch->errors ?? []) }}</td></tr>
        </table>
    </div>

    @if ($batch->status === 'completed')
        <p><a href="{{ route('data_export_center.batches.download', $batch) }}">Download</a></p>
    @endif
@endsection
