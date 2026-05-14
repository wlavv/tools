@extends('permission-role-manager::layouts.module')
@section('module-content')
<div class="prm-card">
    <div style="display:grid;grid-template-columns:1fr 220px auto;gap:8px;align-items:center">
        <form style="display:contents">
            <input class="prm-input" name="q" value="{{ request('q') }}" placeholder="Pesquisar key ou label">
            <select class="prm-select" name="module" onchange="this.form.submit()">
                <option value="">Todos os modulos</option>
                @foreach($modules as $module)
                    <option value="{{ $module }}" @selected(request('module')===$module)>{{ $module }}</option>
                @endforeach
            </select>
        </form>
        <form method="post" action="{{ route('permission_role_manager.permissions.sync') }}">
            @csrf
            <button class="prm-btn"><i class="fa-solid fa-rotate"></i> Sync Base</button>
        </form>
    </div>
</div>
<div class="prm-card"><table class="prm-table"><thead><tr><th>Key</th><th>Modulo</th><th>Risco</th><th>Estado</th><th>Acoes</th></tr></thead><tbody>@foreach($permissions as $permission)<tr><td><strong>{{ $permission->key }}</strong><br><span class="prm-muted">{{ $permission->label }}</span></td><td>{{ $permission->module }}</td><td><span class="prm-badge risk-{{ $permission->risk }}">{{ $permission->risk }}</span></td><td>{{ $permission->is_active ? 'Ativa' : 'Inativa' }}</td><td class="prm-actions"><a class="prm-btn" href="{{ route('permission_role_manager.permissions.edit',$permission) }}"><i class="fa-solid fa-pencil"></i></a><form method="post" action="{{ route('permission_role_manager.permissions.toggle',$permission) }}">@csrf<button class="prm-btn" title="{{ $permission->is_active ? 'Desativar' : 'Ativar' }}" aria-label="{{ $permission->is_active ? 'Desativar' : 'Ativar' }}"><i class="fa-solid {{ $permission->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button></form><form method="post" action="{{ route('permission_role_manager.permissions.destroy',$permission) }}">@csrf @method('DELETE')<button class="prm-btn" onclick="return confirm('Remover permission?')"><i class="fa-solid fa-trash"></i></button></form></td></tr>@endforeach</tbody></table>{{ $permissions->links() }}</div>
@endsection
