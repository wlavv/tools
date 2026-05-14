@extends('data-export-center::layout')

@section('module-content')
    <h1>Perfis de exportação</h1>

    <table>
        <thead>
        <tr>
            <th>Key</th>
            <th>Label</th>
            <th>Type</th>
            <th>Module</th>
            <th>Fields</th>
            <th>Dependencies</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($profiles as $profile)
            <tr>
                <td>{{ $profile['key'] }}</td>
                <td>{{ $profile['label'] }}</td>
                <td><span class="badge">{{ $profile['type'] }}</span></td>
                <td>{{ $profile['module'] }}</td>
                <td>{{ $profile['fields_count'] }}</td>
                <td>{{ $profile['dependencies_count'] }}</td>
                <td><a href="{{ route('data_export_center.profiles.show', $profile['key']) }}">Abrir</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
