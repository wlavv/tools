@extends('data-import-wizard::layout')

@section('module-content')
    @php
        $metricStyles = ['roles', 'users', 'critical', 'permissions'];
        $metricIcons = ['fa-layer-group', 'fa-circle-check', 'fa-triangle-exclamation', 'fa-code-branch'];
    @endphp

    <div class="prm-dashboard-grid">
        @foreach ($readiness['counters'] as $label => $value)
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

    <div class="card">
        <h2>Módulos sem perfil de importação</h2>
        @if (count($readiness['modules_without_profiles']) === 0)
            <p class="muted">Todos os módulos detetados têm pelo menos um perfil importável registado.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>Módulo</th>
                    <th>Estado</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($readiness['modules_without_profiles'] as $module)
                    <tr>
                        <td>{{ $module['name'] }}</td>
                        <td><span class="badge err">sem perfil</span></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <h2>Coerência dos perfis</h2>
        <table>
            <thead>
            <tr>
                <th>Perfil</th>
                <th>Módulo</th>
                <th>Classe</th>
                <th>Campos</th>
                <th>Dependências</th>
                <th>Estado</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($readiness['profiles'] as $profile)
                <tr>
                    <td><a href="{{ route('data_import_wizard.profiles.show', $profile['key']) }}">{{ $profile['label'] }}</a></td>
                    <td>{{ $profile['module'] ?: '-' }}</td>
                    <td><code>{{ $profile['class'] }}</code></td>
                    <td>{{ $profile['headers_count'] }}</td>
                    <td>{{ $profile['dependencies_count'] }}</td>
                    <td>
                        @if ($profile['status'] === 'valid')
                            <span class="badge ok">válido</span>
                        @elseif ($profile['status'] === 'without_fields')
                            <span class="badge warn">sem campos</span>
                        @else
                            <span class="badge err">inválido</span>
                            <ul>
                                @foreach ($profile['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <h2>Últimas importações</h2>
        @if ($recentBatches->isEmpty())
            <p class="muted">Ainda não existem importações.</p>
        @else
            <table>
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Perfil</th>
                    <th>Estado</th>
                    <th>Linhas</th>
                    <th>Criado em</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($recentBatches as $batch)
                    <tr>
                        <td><a href="{{ route('data_import_wizard.batches.show', $batch) }}">#{{ $batch->id }}</a></td>
                        <td>{{ $batch->profile_key }}</td>
                        <td>{{ $batch->status }}</td>
                        <td>{{ $batch->total_rows }}</td>
                        <td>{{ $batch->created_at }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
