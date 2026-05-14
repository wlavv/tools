@extends('layouts.app')

@section('content')
<style>
    .prm-shell{display:grid;grid-template-columns:260px 1fr;gap:18px}.prm-side,.prm-card{border-radius:5px;background:var(--bs-body-bg,#fff);box-shadow:0 8px 24px rgba(15,23,42,.08);border:1px solid rgba(148,163,184,.25)}.prm-side{padding:12px;position:sticky;top:16px;height:max-content}.prm-nav{display:flex;flex-direction:column;gap:6px}.prm-nav a{border-radius:5px;padding:10px 12px;text-decoration:none;color:inherit;display:flex;gap:8px;align-items:center}.prm-nav a.active,.prm-nav a:hover{background:rgba(59,130,246,.10)}.prm-card{padding:16px;margin-bottom:10px}.prm-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:10px}.prm-metric{border-radius:5px;padding:16px;border:1px solid rgba(148,163,184,.25);background:linear-gradient(135deg,rgba(255,255,255,.95),rgba(248,250,252,.75))}.prm-muted{color:#64748b}.prm-badge{border-radius:5px;padding:3px 8px;font-size:12px;display:inline-flex;align-items:center;gap:5px}.risk-low{background:#dcfce7;color:#166534}.risk-medium{background:#dbeafe;color:#1e40af}.risk-high{background:#ffedd5;color:#9a3412}.risk-critical{background:#fee2e2;color:#991b1b}.prm-table{width:100%;border-collapse:separate;border-spacing:0 6px}.prm-table th{font-size:12px;text-transform:uppercase;color:#64748b}.prm-table td,.prm-table th{padding:10px}.prm-table tbody tr{background:rgba(248,250,252,.85)}.prm-table tbody td:first-child{border-radius:5px 0 0 5px}.prm-table tbody td:last-child{border-radius:0 5px 5px 0}.prm-actions{display:flex;gap:6px;flex-wrap:wrap}.prm-btn{border-radius:5px;padding:7px 10px;border:1px solid rgba(148,163,184,.45);background:#fff;text-decoration:none;color:inherit}.prm-btn:hover{background:#f8fafc}.prm-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.prm-form-grid label{display:block;margin-bottom:6px;font-weight:600}.prm-input,.prm-select,.prm-textarea{width:100%;border-radius:5px;border:1px solid rgba(148,163,184,.55);padding:9px 10px;background:var(--bs-body-bg,#fff);color:inherit}.prm-check-list{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.prm-check{border:1px solid rgba(148,163,184,.35);border-radius:5px;padding:8px}.prm-alert{border-radius:5px;padding:10px 12px;margin-bottom:12px}.prm-alert.success{background:#dcfce7;color:#166534}.prm-alert.error{background:#fee2e2;color:#991b1b}@media(max-width:900px){.prm-shell{grid-template-columns:1fr}.prm-grid{grid-template-columns:1fr 1fr}.prm-check-list{grid-template-columns:1fr}}
</style>

<div class="prm-shell">
    <aside class="prm-side">
        <h5 style="margin:4px 8px 12px;">Permission Manager</h5>
        <nav class="prm-nav">
            <?php $route = request()->route()?->getName(); ?>
            <a class="{{ $route === 'permission_role_manager.dashboard' ? 'active' : '' }}" href="{{ route('permission_role_manager.dashboard') }}"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a class="{{ request()->routeIs('permission_role_manager.users*') ? 'active' : '' }}" href="{{ route('permission_role_manager.users.index') }}"><i class="fa-solid fa-users"></i> Users</a>
            <a class="{{ request()->routeIs('permission_role_manager.roles*') ? 'active' : '' }}" href="{{ route('permission_role_manager.roles.index') }}"><i class="fa-solid fa-user-shield"></i> Perfis</a>
            <a class="{{ request()->routeIs('permission_role_manager.route_access*') ? 'active' : '' }}" href="{{ route('permission_role_manager.route_access.index') }}"><i class="fa-solid fa-route"></i> Route Access</a>
            <a class="{{ request()->routeIs('permission_role_manager.matrix*') ? 'active' : '' }}" href="{{ route('permission_role_manager.matrix.index') }}"><i class="fa-solid fa-table-cells"></i> Matrix</a>
            <a class="{{ request()->routeIs('permission_role_manager.permissions*') ? 'active' : '' }}" href="{{ route('permission_role_manager.permissions.index') }}"><i class="fa-solid fa-key"></i> Permissions</a>
            <a class="{{ request()->routeIs('permission_role_manager.inspector*') ? 'active' : '' }}" href="{{ route('permission_role_manager.inspector.index') }}"><i class="fa-solid fa-magnifying-glass-chart"></i> Inspector</a>
            <a class="{{ request()->routeIs('permission_role_manager.audit*') ? 'active' : '' }}" href="{{ route('permission_role_manager.audit.index') }}"><i class="fa-solid fa-clock-rotate-left"></i> Audit Log</a>
            <a class="{{ request()->routeIs('permission_role_manager.settings*') ? 'active' : '' }}" href="{{ route('permission_role_manager.settings.index') }}"><i class="fa-solid fa-cog"></i> Settings</a>
        </nav>
    </aside>
    <main>
        @if(session('success'))<div class="prm-alert success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="prm-alert error">{{ session('error') }}</div>@endif
        @yield('module-content')
    </main>
</div>
@endsection
