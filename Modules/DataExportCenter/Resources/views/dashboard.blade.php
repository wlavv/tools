@extends('data-export-center::layout')

@section('module-content')
    @php
        $metricStyles = ['roles', 'users', 'critical', 'permissions'];
        $metricIcons = ['fa-file-export', 'fa-circle-check', 'fa-triangle-exclamation', 'fa-list-check'];
    @endphp

    <div class="prm-dashboard-grid">
        @foreach ($summary['counters'] as $label => $value)
            @php $metricIndex = $loop->index % count($metricStyles); @endphp
            <div class="prm-dashboard-metric {{ $metricStyles[$metricIndex] }}">
                <div>
                    <div class="prm-dashboard-metric__label">{{ str_replace('_', ' ', $label) }}</div>
                    <div class="prm-dashboard-metric__value">{{ $value }}</div>
                </div>
                <div class="prm-dashboard-metric__icon"><i class="fa-solid {{ $metricIcons[$metricIndex] }}"></i></div>
            </div>
        @endforeach
    </div>

    <div class="panel">
        <h2>Perfis</h2>
        <table>
            <thead>
            <tr>
                <th>Key</th>
                <th>Label</th>
                <th>Type</th>
                <th>Status</th>
                <th>Headers</th>
                <th>Errors</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($summary['profiles'] as $profile)
                <tr>
                    <td>{{ $profile['key'] }}</td>
                    <td>{{ $profile['label'] }}</td>
                    <td><span class="badge">{{ $profile['type'] }}</span></td>
                    <td>{{ $profile['status'] }}</td>
                    <td>{{ $profile['headers_count'] }}</td>
                    <td>{{ implode('; ', $profile['errors'] ?? []) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
