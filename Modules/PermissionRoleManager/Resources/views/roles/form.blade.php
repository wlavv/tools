@extends('permission-role-manager::layouts.module')
@section('module-content')
<form id="lsg-form" method="post" action="{{ $role->exists ? route('permission_role_manager.roles.update',$role) : route('permission_role_manager.roles.store') }}">
    @csrf
    @if($role->exists) @method('PUT') @endif

    <div class="prm-card prm-form-grid">
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
            <input class="prm-input" name="color" value="{{ old('color',$role->color) }}">
        </div>
        <div style="grid-column:1/-1">
            <label>Descricao</label>
            <textarea class="prm-textarea" name="description">{{ old('description',$role->description) }}</textarea>
        </div>
        <label><input type="checkbox" name="is_active" value="1" {{ old('is_active',$role->is_active ?? true) ? 'checked' : '' }}> Ativa</label>
    </div>

    <div class="prm-card">
        <h4>Permissions por Modulo</h4>
        @foreach($permissions->groupBy(fn($permission) => $permission->module ?: 'Sem modulo') as $module => $modulePermissions)
            <div style="border:1px solid rgba(148,163,184,.35);border-radius:5px;padding:12px;margin-top:12px">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap">
                    <div>
                        <strong>{{ $module }}</strong>
                        <div class="prm-muted">{{ $modulePermissions->count() }} permissions</div>
                    </div>
                    <label class="prm-btn" style="cursor:pointer">
                        <input type="checkbox" data-prm-module-toggle="{{ \Illuminate\Support\Str::slug($module) }}">
                        Selecionar modulo
                    </label>
                </div>

                <div class="prm-check-list" style="margin-top:12px">
                    @foreach($modulePermissions as $permission)
                        <label class="prm-check">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                value="{{ $permission->id }}"
                                data-prm-module="{{ \Illuminate\Support\Str::slug($module) }}"
                                {{ in_array($permission->id,$selectedPermissions) ? 'checked' : '' }}
                            >
                            <strong>{{ $permission->key }}</strong><br>
                            <span class="prm-badge risk-{{ $permission->risk }}">{{ $permission->risk }}</span>
                            <span class="prm-muted">{{ $permission->label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
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
