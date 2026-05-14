@extends('data-import-wizard::layout')

@section('module-content')
    <h1>{{ $profile['label'] }}</h1>

    <div class="card">
        <p><strong>Key:</strong> <code>{{ $profile['key'] }}</code></p>
        <p><strong>Classe:</strong> <code>{{ $profile['class'] }}</code></p>
        <p><strong>Módulo:</strong> {{ $profile['module'] ?: '-' }}</p>

        <a class="btn secondary" href="{{ route('data_import_wizard.profiles.template', $profile['key']) }}">Descarregar CSV</a>
        <a class="btn secondary" href="{{ route('data_import_wizard.profiles.template', [$profile['key'], 'examples' => 1]) }}">CSV com exemplo</a>
        <a class="btn" href="{{ route('data_import_wizard.profiles.upload', $profile['key']) }}">Importar</a>
    </div>

    @if (!empty($schema['warnings']))
        <div class="card">
            <h2>Avisos</h2>
            <ul>
                @foreach ($schema['warnings'] as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <h2>Campos CSV</h2>
        <table>
            <thead>
            <tr>
                <th>Campo CSV</th>
                <th>Origem</th>
                <th>Campo original</th>
                <th>Coluna BD</th>
                <th>Tipo</th>
                <th>Obrigatório</th>
                <th>Exemplo</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($schema['fields'] as $field)
                <tr>
                    <td><code>{{ $field['csv_key'] }}</code></td>
                    <td>{{ $field['source_label'] }}</td>
                    <td><code>{{ $field['field_key'] }}</code></td>
                    <td><code>{{ $field['column'] }}</code></td>
                    <td>{{ $field['type'] }}</td>
                    <td>{{ $field['required'] ? 'Sim' : 'Não' }}</td>
                    <td>{{ $field['example'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Grafo de dependências</h2>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Classe</th>
                <th>Prefixo</th>
                <th>Modo</th>
                <th>Foreign key</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($schema['graph']['nodes'] as $node)
                <tr>
                    <td><code>{{ $node['id'] }}</code></td>
                    <td><code>{{ $node['class'] }}</code></td>
                    <td>{{ $node['prefix'] ?: '-' }}</td>
                    <td>{{ $node['mode'] }}</td>
                    <td>{{ $node['foreign_key'] ?: '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
