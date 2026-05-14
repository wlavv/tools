@extends('data-import-wizard::layout')

@section('module-content')
    <h1>Perfis de importação</h1>

    <div class="card">
        @if ($profiles->isEmpty())
            <p>Não existem perfis registados.</p>
            <p class="muted">Adiciona models importáveis em <code>Config/config.php</code>, chave <code>importables</code>.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>Key</th>
                    <th>Label</th>
                    <th>Módulo</th>
                    <th>Classe</th>
                    <th>Campos</th>
                    <th>Dependências</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($profiles as $profile)
                    <tr>
                        <td><code>{{ $profile['key'] }}</code></td>
                        <td>{{ $profile['label'] }}</td>
                        <td>{{ $profile['module'] ?: '-' }}</td>
                        <td><code>{{ $profile['class'] }}</code></td>
                        <td>{{ $profile['fields_count'] }}</td>
                        <td>{{ $profile['dependencies_count'] }}</td>
                        <td>
                            <a class="btn secondary" href="{{ route('data_import_wizard.profiles.show', $profile['key']) }}">Ver</a>
                            <a class="btn secondary" href="{{ route('data_import_wizard.profiles.template', $profile['key']) }}">CSV</a>
                            <a class="btn" href="{{ route('data_import_wizard.profiles.upload', $profile['key']) }}">Importar</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
