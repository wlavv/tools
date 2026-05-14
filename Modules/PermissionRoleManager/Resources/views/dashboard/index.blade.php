@extends('permission-role-manager::layouts.module')
@section('module-content')
<style>
    .prm-dashboard-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:10px}
    .prm-dashboard-metric{position:relative;overflow:hidden;border-radius:5px;padding:16px;border:1px solid rgba(148,163,184,.25);background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(248,250,252,.86));box-shadow:0 8px 24px rgba(15,23,42,.08);display:flex;justify-content:space-between;gap:14px;align-items:center}
    .prm-dashboard-metric__label{font-size:12px;text-transform:uppercase;color:#64748b;font-weight:800;letter-spacing:.04em}
    .prm-dashboard-metric__value{font-size:30px;line-height:1;font-weight:900;color:#0f172a;margin-top:6px}
    .prm-dashboard-metric__icon{width:46px;height:46px;border-radius:5px;display:flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--metric-color,#2563eb) 16%,transparent);color:var(--metric-color,#2563eb);font-size:20px;border:1px solid color-mix(in srgb,var(--metric-color,#2563eb) 28%,transparent)}
    .prm-dashboard-metric.roles{--metric-color:#2563eb}
    .prm-dashboard-metric.permissions{--metric-color:#7c3aed}
    .prm-dashboard-metric.critical{--metric-color:#dc2626}
    .prm-dashboard-metric.users{--metric-color:#16a34a}
    @media(max-width:900px){.prm-dashboard-grid{grid-template-columns:1fr 1fr}}
</style>
<div class="prm-dashboard-grid">
    <div class="prm-dashboard-metric roles">
        <div><div class="prm-dashboard-metric__label">Perfis</div><div class="prm-dashboard-metric__value">{{ $rolesCount }}</div></div>
        <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-user-shield"></i></div>
    </div>
    <div class="prm-dashboard-metric permissions">
        <div><div class="prm-dashboard-metric__label">Permissions</div><div class="prm-dashboard-metric__value">{{ $permissionsCount }}</div></div>
        <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-key"></i></div>
    </div>
    <div class="prm-dashboard-metric critical">
        <div><div class="prm-dashboard-metric__label">Criticas</div><div class="prm-dashboard-metric__value">{{ $criticalPermissionsCount }}</div></div>
        <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
    </div>
    <div class="prm-dashboard-metric users">
        <div><div class="prm-dashboard-metric__label">Users</div><div class="prm-dashboard-metric__value">{{ $usersCount }}</div></div>
        <div class="prm-dashboard-metric__icon"><i class="fa-solid fa-users"></i></div>
    </div>
</div>
<div class="prm-card"><h4>Ultimas alteracoes</h4><table class="prm-table"><thead><tr><th>Data</th><th>Acao</th><th>User</th></tr></thead><tbody>@forelse($recentLogs as $log)<tr><td>{{ $log->created_at }}</td><td>{{ $log->action }}</td><td>{{ $log->user_email }}</td></tr>@empty<tr><td colspan="3">Sem registos.</td></tr>@endforelse</tbody></table></div>
@endsection
