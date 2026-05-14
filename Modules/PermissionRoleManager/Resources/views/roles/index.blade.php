@extends('permission-role-manager::layouts.module')
@section('module-content')
<div class="prm-card">
    <div style="display:grid;grid-template-columns:1fr auto;gap:10px;align-items:center">
        <form>
            @if($includeAutoProfiles)
                <input type="hidden" name="include_auto" value="1">
            @endif
            <input class="prm-input" name="q" value="{{ request('q') }}" placeholder="Pesquisar por nome ou slug">
        </form>

        @if($autoProfilesCount > 0)
            <div class="prm-actions">
                <a class="prm-btn" href="{{ route('permission_role_manager.route_access.index') }}"><i class="fa-solid fa-route"></i> Route Access</a>
                @if($includeAutoProfiles)
                    <a class="prm-btn" href="{{ route('permission_role_manager.roles.index') }}">Ocultar auto</a>
                @else
                    <a class="prm-btn" href="{{ route('permission_role_manager.roles.index', ['include_auto' => 1]) }}">Mostrar auto</a>
                @endif
            </div>
        @endif
    </div>
    @if($autoProfilesCount > 0)
        <p class="prm-muted" style="margin:8px 0 0">
            Existem {{ $autoProfilesCount }} perfis automaticos antigos. A lista mostra perfis manuais por defeito.
        </p>
    @endif
</div>

<div class="prm-card">
    <table class="prm-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Slug</th>
                <th>Permissions</th>
                <th>Estado</th>
                <th>Acoes</th>
            </tr>
        </thead>
        <tbody>
            @foreach($roles as $role)
                <tr>
                    <td>
                        <strong>{{ $role->name }}</strong><br>
                        <span class="prm-muted">{{ $role->description }}</span>
                    </td>
                    <td>{{ $role->slug }}</td>
                    <td>{{ $role->permissions_count }}</td>
                    <td>{{ $role->is_active ? 'Ativa' : 'Inativa' }}</td>
                    <td class="prm-actions">
                        <a class="prm-btn" href="{{ route('permission_role_manager.roles.edit',$role) }}"><i class="fa-solid fa-pencil"></i></a>
                        <form method="post" action="{{ route('permission_role_manager.roles.toggle',$role) }}">
                            @csrf
                            <button
                                class="prm-btn"
                                title="{{ $role->is_active ? 'Desativar' : 'Ativar' }}"
                                aria-label="{{ $role->is_active ? 'Desativar' : 'Ativar' }}"
                            >
                                <i class="fa-solid {{ $role->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                            </button>
                        </form>
                        <form method="post" action="{{ route('permission_role_manager.roles.destroy',$role) }}">
                            @csrf
                            @method('DELETE')
                            <button class="prm-btn" onclick="return confirm('Remover perfil?')"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    {{ $roles->withQueryString()->links() }}
</div>
@endsection
