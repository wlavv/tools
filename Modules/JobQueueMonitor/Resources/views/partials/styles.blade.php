<style>
    .jqm-wrap { --jqm-radius: 5px; }
    .jqm-card { border-radius: var(--jqm-radius); border: 1px solid rgba(148,163,184,.22); box-shadow: 0 12px 30px rgba(15,23,42,.08); background: linear-gradient(145deg, rgba(255,255,255,.96), rgba(248,250,252,.90)); }
    .jqm-card .card-body { padding: 1rem; }
    .prm-dashboard-grid { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:12px; margin-bottom:16px; }
    .prm-dashboard-metric { position:relative; overflow:hidden; border-radius:0; padding:16px; min-height:104px; border:1px solid rgba(148,163,184,.25); background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(248,250,252,.86)); box-shadow:0 8px 24px rgba(15,23,42,.08); display:flex; justify-content:space-between; gap:14px; align-items:center; }
    .prm-dashboard-metric__label { font-size:12px; text-transform:uppercase; color:#64748b; font-weight:800; letter-spacing:.04em; }
    .prm-dashboard-metric__value { font-size:30px; line-height:1; font-weight:900; color:#0f172a; margin-top:6px; }
    .prm-dashboard-metric__icon { width:46px; height:46px; border-radius:0; display:flex; align-items:center; justify-content:center; background:color-mix(in srgb,var(--metric-color,#2563eb) 16%,transparent); color:var(--metric-color,#2563eb); font-size:20px; border:1px solid color-mix(in srgb,var(--metric-color,#2563eb) 28%,transparent); flex:0 0 46px; }
    .prm-dashboard-metric.roles { --metric-color:#2563eb; }
    .prm-dashboard-metric.permissions { --metric-color:#7c3aed; }
    .prm-dashboard-metric.critical { --metric-color:#dc2626; }
    .prm-dashboard-metric.users { --metric-color:#16a34a; }
    .jqm-kpi-grid { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:12px; margin-bottom:16px; }
    .jqm-kpi-metric { display:flex; align-items:center; gap:12px; min-height:104px; padding:16px; border-radius:0; border:1px solid var(--jqm-kpi-border); background:linear-gradient(135deg,var(--jqm-kpi-bg),rgba(255,255,255,.72)); box-shadow:0 12px 28px rgba(15,23,42,.08); }
    .jqm-kpi-icon { width:42px; height:42px; flex:0 0 42px; display:inline-flex; align-items:center; justify-content:center; border-radius:0; color:var(--jqm-kpi-color); background:var(--jqm-kpi-icon-bg); font-size:1.15rem; }
    .jqm-kpi-metric--blue { --jqm-kpi-color:#2563eb; --jqm-kpi-bg:rgba(37,99,235,.10); --jqm-kpi-border:rgba(37,99,235,.22); --jqm-kpi-icon-bg:rgba(37,99,235,.14); }
    .jqm-kpi-metric--green { --jqm-kpi-color:#16a34a; --jqm-kpi-bg:rgba(22,163,74,.10); --jqm-kpi-border:rgba(22,163,74,.22); --jqm-kpi-icon-bg:rgba(22,163,74,.14); }
    .jqm-kpi-metric--red { --jqm-kpi-color:#dc2626; --jqm-kpi-bg:rgba(220,38,38,.10); --jqm-kpi-border:rgba(220,38,38,.22); --jqm-kpi-icon-bg:rgba(220,38,38,.14); }
    .jqm-kpi-metric--cyan { --jqm-kpi-color:#0891b2; --jqm-kpi-bg:rgba(8,145,178,.10); --jqm-kpi-border:rgba(8,145,178,.22); --jqm-kpi-icon-bg:rgba(8,145,178,.14); }
    .jqm-kpi-metric--amber { --jqm-kpi-color:#d97706; --jqm-kpi-bg:rgba(217,119,6,.10); --jqm-kpi-border:rgba(217,119,6,.22); --jqm-kpi-icon-bg:rgba(217,119,6,.14); }
    .jqm-kpi-metric--purple { --jqm-kpi-color:#7c3aed; --jqm-kpi-bg:rgba(124,58,237,.10); --jqm-kpi-border:rgba(124,58,237,.22); --jqm-kpi-icon-bg:rgba(124,58,237,.14); }
    .jqm-kpi { min-height: 112px; }
    .jqm-kpi-value { font-size: 1.8rem; font-weight: 800; line-height: 1; }
    .jqm-kpi-label { color: #64748b; font-size: .76rem; font-weight:800; text-transform:uppercase; letter-spacing:.04em; margin-bottom:.35rem; }
    .jqm-badge { border-radius: 999px; padding: .25rem .55rem; font-size: .76rem; font-weight: 700; }
    .jqm-badge-success { background: rgba(34,197,94,.12); color: #15803d; }
    .jqm-badge-danger { background: rgba(239,68,68,.12); color: #b91c1c; }
    .jqm-badge-warning { background: rgba(245,158,11,.14); color: #b45309; }
    .jqm-badge-info { background: rgba(59,130,246,.12); color: #1d4ed8; }
    .jqm-table th { font-size: .75rem; color: #64748b; text-transform: uppercase; letter-spacing: .04em; border-top: 0; }
    .jqm-table td { vertical-align: middle; }
    .jqm-pre { max-height: 460px; overflow: auto; border-radius: 5px; padding: 1rem; background: #0f172a; color: #e2e8f0; font-size: .82rem; }
    .jqm-alert { color: var(--text-primary, #0f172a) !important; border-color: rgba(59,130,246,.28) !important; background: rgba(59,130,246,.10) !important; }
    .jqm-alert code { color: inherit !important; font-weight: 800; background: rgba(59,130,246,.12); padding: 2px 5px; }
    body.theme-dark .jqm-alert,
    body[data-theme="dark"] .jqm-alert { color: #dbeafe !important; background: rgba(59,130,246,.14) !important; }
    @media (max-width: 1399.98px) { .prm-dashboard-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width: 767.98px) { .prm-dashboard-grid { grid-template-columns:1fr; } }
    @media (max-width: 1399.98px) { .jqm-kpi-grid { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width: 767.98px) { .jqm-kpi-grid { grid-template-columns:1fr; } }
</style>
