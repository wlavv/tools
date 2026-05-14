@extends('data-import-wizard::layout')

@section('module-content')
    <h1>Histórico de importações</h1>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Perfil</th>
                <th>Estado</th>
                <th>Modo</th>
                <th>Total</th>
                <th>Válidas</th>
                <th>Erros</th>
                <th>Criado em</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($batches as $batch)
                <tr>
                    <td><a href="{{ route('data_import_wizard.batches.show', $batch) }}">#{{ $batch->id }}</a></td>
                    <td>{{ $batch->profile_key }}</td>
                    <td>{{ $batch->status }}</td>
                    <td>{{ $batch->mode }}</td>
                    <td>{{ $batch->total_rows }}</td>
                    <td>{{ $batch->valid_rows }}</td>
                    <td>{{ $batch->error_rows }}</td>
                    <td>{{ $batch->created_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        {{ $batches->links() }}
    </div>
@endsection
