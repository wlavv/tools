@extends('permission-role-manager::layouts.module')
@section('module-content')
<style>
    .prm-check-list--compact {
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    }

    .prm-check-list--compact .prm-check {
        display: flex;
        align-items: center;
        gap: 8px;
        min-height: 42px;
    }

    .prm-check-list--compact .prm-permission-label__name {
        font-size: 13px;
    }

    .prm-role-main-fields {
        grid-template-columns: minmax(180px, 1.2fr) minmax(150px, 1fr) minmax(90px, .55fr) 76px auto;
        align-items: end;
    }

    .prm-color-input {
        min-height: 42px;
        padding: 4px;
    }

    .prm-module-permissions > summary {
        color: inherit;
    }

    .prm-module-permissions.alert-danger > summary,
    .prm-module-permissions.alert-danger > summary .prm-muted {
        color: #7f1d1d;
    }

    .prm-module-permissions.alert-warning > summary,
    .prm-module-permissions.alert-warning > summary .prm-muted {
        color: #78350f;
    }
</style>
<form id="lsg-form" method="post" action="{{ $role->exists ? route('permission_role_manager.roles.update',$role) : route('permission_role_manager.roles.store') }}">
    @csrf
    @if($role->exists) @method('PUT') @endif

    <div class="prm-card prm-form-grid prm-role-main-fields">
        <div>
            <label>Nome</label>
            <input class="prm-input" name="name" value="{{ old('name',$role->name) }}" required>
        </div>
        <div>
            <label>Slug</label>
            <input class="prm-input" name="slug" value="{{ old('slug',$role->slug) }}">
        </div>
        <div>
            <label>Guard</label>
            <input class="prm-input" name="guard_name" value="{{ old('guard_name',$role->guard_name ?: 'web') }}" required>
        </div>
        <div>
            <label>Cor</label>
            <input class="prm-input prm-color-input" type="color" name="color" value="{{ old('color',$role->color ?: '#111827') }}">
        </div>
        <label style="margin:0 0 9px"><input type="checkbox" name="is_active" value="1" {{ old('is_active',$role->is_active ?? true) ? 'checked' : '' }}> Ativa</label>
        <div style="grid-column:1/-1">
            <label>Descricao</label>
            <input class="prm-input" name="description" value="{{ old('description',$role->description) }}">
        </div>
    </div>

    <div class="prm-card">
        <h4>Permissions por Modulo</h4>
        @foreach($permissions->groupBy(fn($permission) => $permission->module ?: 'Sem modulo') as $module => $modulePermissions)
            @php
                $modulePermissionIds = $modulePermissions->pluck('id')->all();
                $selectedModuleCount = count(array_intersect($modulePermissionIds, $selectedPermissions));
                $modulePermissionsCount = $modulePermissions->count();
                $missingModuleCount = $modulePermissionsCount - $selectedModuleCount;
                $moduleStateClass = $selectedModuleCount === 0
                    ? 'alert-danger'
                    : ($missingModuleCount > 0 ? 'alert-warning' : '');
            @endphp
            <details class="prm-module-permissions {{ $moduleStateClass }}" style="border:1px solid rgba(148,163,184,.35);border-radius:5px;padding:12px;margin-top:12px">
                <summary style="cursor:pointer;list-style:none">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
                    <div>
                        <strong>{{ $module }}</strong>
                        <div class="prm-muted">
                            {{ $selectedModuleCount }} / {{ $modulePermissionsCount }} permissions
                            @if($missingModuleCount > 0)
                                - faltam {{ $missingModuleCount }}
                            @endif
                        </div>
                    </div>
                    <label class="prm-btn" style="cursor:pointer" onclick="event.stopPropagation()">
                        <input type="checkbox" data-prm-module-toggle="{{ \Illuminate\Support\Str::slug($module) }}">
                        Selecionar modulo
                    </label>
                </div>
                </summary>

                <div class="prm-check-list prm-check-list--compact" style="margin-top:12px">
                    @foreach($modulePermissions as $permission)
                        <label class="prm-check">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->id }}"
                                data-prm-module="{{ \Illuminate\Support\Str::slug($module) }}"
                                {{ in_array($permission->id,$selectedPermissions) ? 'checked' : '' }}
                            >
                            @include('permission-role-manager::permissions._permission-label', ['permission' => $permission, 'contextModule' => $module])
                        </label>
                    @endforeach
                </div>
            </details>
        @endforeach
    </div>
</form>

<script>
document.querySelectorAll('[data-prm-module-toggle]').forEach(function (toggle) {
    var key = toggle.getAttribute('data-prm-module-toggle');
    var items = Array.from(document.querySelectorAll('[data-prm-module="' + key + '"]'));

    function refreshToggle() {
        var checked = items.filter(function (item) { return item.checked; }).length;
        toggle.checked = checked === items.length && items.length > 0;
        toggle.indeterminate = checked > 0 && checked < items.length;
    }

    toggle.addEventListener('change', function () {
        items.forEach(function (item) {
            item.checked = toggle.checked;
        });
        refreshToggle();
    });

    items.forEach(function (item) {
        item.addEventListener('change', refreshToggle);
    });

    refreshToggle();
});
</script>
@endsection
