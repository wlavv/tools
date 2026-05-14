@extends('permission-role-manager::layouts.module')
@section('module-content')
@if($autoProfilesCount > 0)
    <div class="prm-card">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
            <div>
                <h4>Perfis automaticos detetados</h4>
                <p class="prm-muted">{{ $autoProfilesCount }} perfis auto gerados por modulo podem ser arquivados para ficar apenas com perfis manuais.</p>
            </div>
            <form method="post" action="{{ route('permission_role_manager.route_access.auto_profiles.archive') }}">
                @csrf
                <button class="prm-btn" onclick="return confirm('Arquivar perfis automaticos route-access-*?')"><i class="fa-solid fa-box-archive"></i> Arquivar perfis auto</button>
            </form>
        </div>
    </div>
@endif

@forelse($groups as $group)
    <div class="prm-card">
        <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap">
            <div>
                <h4>{{ $group['module'] }}</h4>
                <p class="prm-muted">
                    {{ $group['routes']->count() }} rotas,
                    {{ $group['permissions_total'] }} permissions sincronizadas
                </p>
            </div>
            <div class="prm-actions">
                <form method="post" action="{{ route('permission_role_manager.route_access.permissions.sync') }}">
                    @csrf
                    <input type="hidden" name="modules[]" value="{{ $group['module'] }}">
                    <button class="prm-btn"><i class="fa-solid fa-key"></i> Sync Permissions</button>
                </form>
            </div>
        </div>
        <div style="overflow:auto">
            <table class="prm-table">
                <thead>
                    <tr>
                        <th>Metodo</th>
                        <th>URI</th>
                        <th>Nome</th>
                        <th>Permission</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($group['routes'] as $route)
                        <tr>
                            <td>{{ implode('|', $route['methods']) }}</td>
                            <td><strong>/{{ $route['uri'] }}</strong></td>
                            <td>{{ $route['name'] ?? '-' }}</td>
                            <td><span class="prm-muted">{{ $route['permission_key'] }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="prm-card">
        <p class="prm-muted">Nao foram encontradas rotas de modulos.</p>
    </div>
@endforelse
@endsection
