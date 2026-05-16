@extends('permission-role-manager::layouts.module')
@section('module-content')
<style>
    .prm-module-state{font-weight:700}
    .prm-module-state.is-empty{background:#fee2e2;color:#991b1b;border-color:#fecaca}
    .prm-module-state.is-partial{background:#fef3c7;color:#92400e;border-color:#fde68a}
    .prm-module-state.is-full{background:#dcfce7;color:#166534;border-color:#bbf7d0}
</style>
<div class="prm-card">
    <form>
        <select class="prm-select" name="module" onchange="this.form.submit()">
            <option value="">Todos os modulos</option>
            @foreach($modules as $module)
                <option value="{{ $module }}" @selected(request('module')===$module)>{{ $module }}</option>
            @endforeach
        </select>
    </form>
</div>

@forelse($groupedPermissions as $module => $modulePermissions)
    @php
        $moduleKey = 'prm-module-' . \Illuminate\Support\Str::slug($module);
    @endphp
    <details class="prm-card">
        <summary style="cursor:pointer;list-style:none">
            <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;flex-wrap:wrap">
                <div>
                    <h4 style="margin:0">{{ $module }}</h4>
                    <p class="prm-muted" style="margin:4px 0 0">{{ $modulePermissions->count() }} permissions</p>
                </div>
                <div class="prm-actions">
                    @foreach($roles as $role)
                        @php
                            $stats = $moduleRoleStats[$module][$role->id] ?? ['assigned' => 0, 'total' => $modulePermissions->count()];
                            $isFull = $stats['total'] > 0 && $stats['assigned'] === $stats['total'];
                            $isPartial = $stats['assigned'] > 0 && !$isFull;
                            $stateClass = $isFull ? 'is-full' : ($isPartial ? 'is-partial' : 'is-empty');
                        @endphp
                        <form method="post" action="{{ route('permission_role_manager.matrix.module.toggle') }}" onclick="event.stopPropagation()" data-prm-matrix-module-form>
                            @csrf
                            <input type="hidden" name="role_id" value="{{ $role->id }}">
                            <input type="hidden" name="module" value="{{ $module }}">
                            <button
                                class="prm-btn prm-module-state {{ $stateClass }}"
                                title="{{ $role->name }}"
                                data-prm-module-state
                                data-role-id="{{ $role->id }}"
                                data-module="{{ $module }}"
                                data-total="{{ $stats['total'] }}"
                                data-assigned="{{ $stats['assigned'] }}"
                            >
                                <span data-prm-module-label>{{ $role->name }}: {{ $isFull ? 'completo' : ($isPartial ? $stats['assigned'].'/'.$stats['total'] : 'vazio') }}</span>
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        </summary>

        <div id="{{ $moduleKey }}" style="overflow:auto;margin-top:14px">
            <table class="prm-table">
                <thead>
                    <tr>
                        <th>Permission</th>
                        @foreach($roles as $role)
                            <th>{{ $role->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($modulePermissions as $permission)
                        <tr>
                            <td>
                                @include('permission-role-manager::permissions._permission-label', ['permission' => $permission])
                            </td>
                            @foreach($roles as $role)
                                <td>
                                    <form method="post" action="{{ route('permission_role_manager.matrix.toggle') }}" data-prm-matrix-permission-form>
                                        @csrf
                                        <input type="hidden" name="role_id" value="{{ $role->id }}">
                                        <input type="hidden" name="permission_id" value="{{ $permission->id }}">
                                        <button
                                            class="prm-btn"
                                            data-prm-permission-state
                                            data-role-id="{{ $role->id }}"
                                            data-permission-id="{{ $permission->id }}"
                                            data-module="{{ $module }}"
                                            data-assigned="{{ isset($assigned[$role->id.':'.$permission->id]) ? 1 : 0 }}"
                                        >{{ isset($assigned[$role->id.':'.$permission->id]) ? 'sim' : 'nao' }}</button>
                                    </form>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </details>
@empty
    <div class="prm-card">
        <p class="prm-muted">Sem permissions para mostrar.</p>
    </div>
@endforelse

<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    function stateText(assigned, total) {
        if (assigned === total && total > 0) {
            return 'completo';
        }

        if (assigned > 0) {
            return assigned + '/' + total;
        }

        return 'vazio';
    }

    function applyModuleState(button, assigned, total) {
        button.dataset.assigned = String(assigned);
        button.classList.remove('is-empty', 'is-partial', 'is-full');

        if (assigned === total && total > 0) {
            button.classList.add('is-full');
        } else if (assigned > 0) {
            button.classList.add('is-partial');
        } else {
            button.classList.add('is-empty');
        }

        var label = button.querySelector('[data-prm-module-label]');
        if (label) {
            label.textContent = button.getAttribute('title') + ': ' + stateText(assigned, total);
        }
    }

    function selectorValue(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return CSS.escape(value);
        }

        return String(value).replace(/"/g, '\\"');
    }

    function recalcModule(roleId, module) {
        var permissionButtons = Array.from(document.querySelectorAll('[data-prm-permission-state][data-role-id="' + roleId + '"][data-module="' + selectorValue(module) + '"]'));
        var assigned = permissionButtons.filter(function (button) {
            return button.dataset.assigned === '1';
        }).length;
        var stateButton = document.querySelector('[data-prm-module-state][data-role-id="' + roleId + '"][data-module="' + selectorValue(module) + '"]');

        if (stateButton) {
            applyModuleState(stateButton, assigned, permissionButtons.length);
        }
    }

    async function submitAjax(form) {
        var buttons = form.querySelectorAll('button');
        buttons.forEach(function (button) { button.disabled = true; });

        try {
            var response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            });

            if (!response.ok) {
                throw new Error('Pedido falhou.');
            }

            return await response.json();
        } finally {
            buttons.forEach(function (button) { button.disabled = false; });
        }
    }

    document.querySelectorAll('[data-prm-matrix-permission-form]').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            var button = form.querySelector('[data-prm-permission-state]');

            try {
                var result = await submitAjax(form);
                button.dataset.assigned = result.assigned ? '1' : '0';
                button.textContent = result.assigned ? 'sim' : 'nao';
                recalcModule(button.dataset.roleId, button.dataset.module);
            } catch (error) {
                form.submit();
            }
        });
    });

    document.querySelectorAll('[data-prm-matrix-module-form]').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            var stateButton = form.querySelector('[data-prm-module-state]');

            try {
                var result = await submitAjax(form);
                var assignedValue = result.assigned ? '1' : '0';
                result.permission_ids.forEach(function (permissionId) {
                    var permissionButton = document.querySelector('[data-prm-permission-state][data-role-id="' + result.role_id + '"][data-permission-id="' + permissionId + '"]');
                    if (permissionButton) {
                        permissionButton.dataset.assigned = assignedValue;
                        permissionButton.textContent = result.assigned ? 'sim' : 'nao';
                    }
                });
                applyModuleState(stateButton, result.assigned_count, result.total);
            } catch (error) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
