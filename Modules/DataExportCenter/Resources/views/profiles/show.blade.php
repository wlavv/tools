@extends('data-export-center::layout')

@section('module-content')
    <h1>{{ $profile['label'] }}</h1>
    <p class="muted">{{ $profile['key'] }} · {{ $profile['type'] }}</p>

    <div class="panel">
        <h2>Executar export</h2>
        <form method="post" action="{{ route('data_export_center.profiles.exports.store', $profile['key']) }}">
            @csrf
            <div class="actions">
                <label>Formato</label>
                <select name="format">
                    @foreach (config('data-export-center.allowed_formats', ['csv']) as $format)
                        <option value="{{ $format }}">{{ strtoupper($format) }}</option>
                    @endforeach
                </select>
                <button type="submit">Exportar</button>
            </div>
        </form>
    </div>

    @if ($schema)
        <div class="panel">
            <h2>Campos</h2>
            <table>
                <thead>
                <tr>
                    <th>Header</th>
                    <th>Label</th>
                    <th>Source</th>
                    <th>Column</th>
                    <th>Type</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($schema['fields'] as $field)
                    <tr>
                        <td>{{ $field['csv_key'] }}</td>
                        <td>{{ $field['label'] }}</td>
                        <td>{{ $field['source_label'] }}</td>
                        <td>{{ $field['column'] }}</td>
                        <td>{{ $field['type'] }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="panel">
            <h2>Grafo de dependências</h2>
            <table>
                <thead>
                <tr>
                    <th>Parent</th>
                    <th>Child</th>
                    <th>Foreign key</th>
                    <th>Owner key</th>
                    <th>Required</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($schema['graph']['edges'] as $edge)
                    <tr>
                        <td>{{ $edge['parent_id'] }}</td>
                        <td>{{ $edge['child_id'] }}</td>
                        <td>{{ $edge['foreign_key'] }}</td>
                        <td>{{ $edge['owner_key'] }}</td>
                        <td>{{ $edge['required'] ? 'yes' : 'no' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="panel">
            <p>Perfil baseado em SQL ou builder dinâmico. A estrutura final é determinada pela query.</p>
        </div>
    @endif
@endsection
