<style>
    .mh-shell { width: 100%; display: flex; flex-direction: column; gap: 20px; }
    .mh-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
    .mh-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
    .mh-detail-grid { display: grid; grid-template-columns: minmax(260px, 360px) minmax(0, 1fr); gap: 14px; align-items: start; }
    .mh-card { border-radius: 5px; border: 1px solid var(--border-soft, rgba(255, 255, 255, .08)) !important; box-shadow: var(--shadow-soft, 0 18px 40px rgba(0, 0, 0, .18)); background: var(--bg-card, linear-gradient(180deg, rgba(31, 35, 42, .98), rgba(25, 31, 39, .98))); color: var(--text-primary, #e2e8f0); }
    .mh-panel { padding: 22px; }
    .mh-card-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, .08); }
    .mh-title { margin: 0; color: #f8fafc; font-size: 18px; line-height: 1.25; font-weight: 800; }
    .mh-subtitle { margin-top: 4px; color: #94a3b8; font-size: 12px; line-height: 1.5; }
    .mh-kpi { padding: 16px; min-height: 116px; display: flex; flex-direction: column; justify-content: space-between; }
    .mh-kpi.prm-dashboard-metric { position: relative; overflow: hidden; border-radius: 0 !important; min-height: 116px; border: 1px solid rgba(148, 163, 184, .25) !important; background: linear-gradient(135deg, rgba(255, 255, 255, .96), rgba(248, 250, 252, .86)) !important; box-shadow: 0 8px 24px rgba(15, 23, 42, .08) !important; }
    .mh-kpi-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .mh-kpi-icon { width: 36px; height: 36px; border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; border: 1px solid rgba(255, 255, 255, .08); background: rgba(255, 255, 255, .05); color: #93c5fd; }
    .prm-dashboard-metric__icon { width: 46px; height: 46px; border-radius: 0; display: flex; align-items: center; justify-content: center; background: color-mix(in srgb, var(--metric-color, #2563eb) 16%, transparent); color: var(--metric-color, #2563eb); font-size: 20px; border: 1px solid color-mix(in srgb, var(--metric-color, #2563eb) 28%, transparent); flex: 0 0 46px; }
    .prm-dashboard-metric.roles { --metric-color: #2563eb; }
    .prm-dashboard-metric.permissions { --metric-color: #7c3aed; }
    .prm-dashboard-metric.critical { --metric-color: #dc2626; }
    .prm-dashboard-metric.users { --metric-color: #16a34a; }
    .mh-kpi .label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; font-weight: 800; }
    .mh-kpi .value { font-size: 30px; line-height: 1; font-weight: 800; color: #f8fafc; }
    .mh-kpi .hint { color: #94a3b8; font-size: 12px; }
    .mh-badge { border-radius: 5px; padding: 5px 9px; font-size: 12px; font-weight: 800; display: inline-flex; align-items: center; gap: 7px; white-space: nowrap; }
    .mh-badge:before { content: ""; width: 7px; height: 7px; border-radius: 99px; background: currentColor; opacity: .85; }
    .mh-broken { background: rgba(248, 113, 113, .13); color: #fca5a5; }
    .mh-incomplete { background: rgba(251, 191, 36, .14); color: #fcd34d; }
    .mh-functional { background: rgba(96, 165, 250, .13); color: #93c5fd; }
    .mh-enhanced { background: rgba(52, 211, 153, .13); color: #86efac; }
    .mh-pill { border-radius: 5px; padding: 4px 8px; background: rgba(255, 255, 255, .06); border: 1px solid rgba(255, 255, 255, .07); color: #cbd5e1; font-size: 12px; display: inline-flex; align-items: center; margin: 2px; min-height: 25px; }
    .mh-pills { display: flex; flex-wrap: wrap; gap: 4px; }
    .mh-table-wrap { margin-top: 4px; border: 1px solid var(--border-soft, rgba(255, 255, 255, .08)) !important; border-radius: 5px; background: var(--bg-card-2, rgba(255, 255, 255, .025)); box-shadow: none; }
    .mh-table { margin-bottom: 0; color: #e2e8f0; }
    .mh-table th { font-size: 11px; text-transform: uppercase; color: #94a3b8; letter-spacing: .04em; border-top: 0; border-bottom-color: rgba(255, 255, 255, .08); white-space: nowrap; padding: 13px 14px; background: rgba(255, 255, 255, .035); }
    .mh-table td { vertical-align: middle; border-top-color: rgba(255, 255, 255, .06); padding: 13px 14px; }
    .mh-module-name { color: #f8fafc; font-weight: 800; }
    .mh-muted { color: #94a3b8 !important; }
    .mh-path { max-width: 100%; word-break: break-word; }
    .mh-progress-wrap { display: flex; align-items: center; gap: 10px; min-width: 160px; }
    .mh-progress { height: 8px; border-radius: 5px; overflow: hidden; background: rgba(255, 255, 255, .08); flex: 1 1 auto; }
    .mh-progress > span { display: block; height: 100%; background: linear-gradient(90deg, #60a5fa, #34d399); border-radius: inherit; }
    .mh-progress-value { min-width: 38px; text-align: right; color: #94a3b8; font-size: 12px; }
    .mh-icon-btn { width: 34px; height: 34px; padding: 0; display: inline-flex; align-items: center; justify-content: center; }
    .mh-card .btn.btn-outline-primary,
    .mh-card .lsg-action-btn.lsg-action-btn--primary {
        background: rgba(37, 99, 235, .24) !important;
        border-color: rgba(96, 165, 250, .76) !important;
        color: #eff6ff !important;
    }
    .mh-card .btn.btn-outline-primary:hover,
    .mh-card .lsg-action-btn.lsg-action-btn--primary:hover {
        background: rgba(37, 99, 235, .34) !important;
        border-color: rgba(96, 165, 250, .9) !important;
        color: #ffffff !important;
    }
    .mh-card .btn.btn-outline-primary i,
    .mh-card .lsg-action-btn.lsg-action-btn--primary i,
    .mh-card .lsg-action-btn.lsg-action-btn--primary .lsg-action-btn__icon {
        color: #eff6ff !important;
    }
    .mh-mini-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; min-width: 0; }
    .mh-mini-stat { min-width: 0; padding: 10px 8px; text-align: center; border-radius: 5px; border: 1px solid rgba(255, 255, 255, .08); background: rgba(255, 255, 255, .04); overflow: hidden; }
    .mh-mini-stat span { display: block; max-width: 100%; color: #94a3b8; font-size: 10px; line-height: 1.2; font-weight: 800; text-transform: uppercase; letter-spacing: 0; overflow-wrap: anywhere; }
    .mh-mini-stat strong { display: block; margin-top: 4px; color: #f8fafc; font-size: 16px; }
    .mh-empty { padding: 34px 16px; text-align: center; color: #94a3b8; }
    .mh-recommendation { display: flex; gap: 10px; align-items: flex-start; padding: 12px; border-radius: 5px; border: 1px solid rgba(255, 255, 255, .08); background: rgba(255, 255, 255, .04); margin-bottom: 8px; }
    .mh-rec-type { flex: 0 0 auto; }

    body.theme-light .mh-card,
    body[data-theme="light"] .mh-card { border-color: var(--border-soft, rgba(15, 23, 42, .08)) !important; box-shadow: var(--shadow-soft, 0 18px 40px rgba(15, 23, 42, .08)); background: var(--bg-card, #ffffff); color: #334155; }
    body.theme-light .mh-title,
    body[data-theme="light"] .mh-title,
    body.theme-light .mh-kpi .value,
    body[data-theme="light"] .mh-kpi .value,
    body.theme-light .mh-module-name,
    body[data-theme="light"] .mh-module-name,
    body.theme-light .mh-mini-stat strong,
    body[data-theme="light"] .mh-mini-stat strong { color: #0f172a; }
    body.theme-light .mh-subtitle,
    body[data-theme="light"] .mh-subtitle,
    body.theme-light .mh-muted,
    body[data-theme="light"] .mh-muted,
    body.theme-light .mh-progress-value,
    body[data-theme="light"] .mh-progress-value { color: #64748b !important; }
    body.theme-light .mh-kpi-icon,
    body[data-theme="light"] .mh-kpi-icon,
    body.theme-light .mh-pill,
    body[data-theme="light"] .mh-pill,
    body.theme-light .mh-mini-stat,
    body[data-theme="light"] .mh-mini-stat,
    body.theme-light .mh-recommendation,
    body[data-theme="light"] .mh-recommendation { background: rgba(15, 23, 42, .04); border-color: rgba(15, 23, 42, .08); color: #334155; }
    body.theme-light .mh-card-head,
    body[data-theme="light"] .mh-card-head { border-bottom-color: rgba(15, 23, 42, .08); }
    body.theme-light .mh-table-wrap,
    body[data-theme="light"] .mh-table-wrap { background: var(--bg-card-2, #f7f9fc); border-color: var(--border-soft, rgba(15, 23, 42, .08)) !important; box-shadow: none; }
    body.theme-light .mh-table,
    body[data-theme="light"] .mh-table { color: #334155; }
    body.theme-light .mh-table th,
    body[data-theme="light"] .mh-table th { color: #64748b; border-bottom-color: rgba(15, 23, 42, .08); }
    body.theme-light .mh-table td,
    body[data-theme="light"] .mh-table td { border-top-color: rgba(15, 23, 42, .06); }
    body.theme-light .mh-progress,
    body[data-theme="light"] .mh-progress { background: rgba(15, 23, 42, .08); }
    body.theme-light .mh-broken,
    body[data-theme="light"] .mh-broken { color: #991b1b; }
    body.theme-light .mh-card .btn.btn-outline-primary,
    body[data-theme="light"] .mh-card .btn.btn-outline-primary,
    body.theme-light .mh-card .lsg-action-btn.lsg-action-btn--primary,
    body[data-theme="light"] .mh-card .lsg-action-btn.lsg-action-btn--primary { background: rgba(37, 99, 235, .13) !important; border-color: rgba(37, 99, 235, .70) !important; color: #1d4ed8 !important; }
    body.theme-light .mh-card .btn.btn-outline-primary i,
    body[data-theme="light"] .mh-card .btn.btn-outline-primary i,
    body.theme-light .mh-card .lsg-action-btn.lsg-action-btn--primary i,
    body[data-theme="light"] .mh-card .lsg-action-btn.lsg-action-btn--primary i,
    body.theme-light .mh-card .lsg-action-btn.lsg-action-btn--primary .lsg-action-btn__icon,
    body[data-theme="light"] .mh-card .lsg-action-btn.lsg-action-btn--primary .lsg-action-btn__icon { color: #1d4ed8 !important; }
    body.theme-light .mh-incomplete,
    body[data-theme="light"] .mh-incomplete { color: #92400e; }
    body.theme-light .mh-functional,
    body[data-theme="light"] .mh-functional { color: #1d4ed8; }
    body.theme-light .mh-enhanced,
    body[data-theme="light"] .mh-enhanced { color: #047857; }

    @media (max-width: 1199.98px) {
        .mh-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .mh-detail-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 767.98px) {
        .mh-grid,
        .mh-grid-3,
        .mh-mini-stats { grid-template-columns: 1fr; }
        .mh-card-head { align-items: flex-start; flex-direction: column; }
        .mh-card-head form,
        .mh-card-head .lsg-action-btn { width: 100%; }
        .mh-panel { padding: 16px; }
    }
</style>
