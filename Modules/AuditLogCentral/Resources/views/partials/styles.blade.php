<style>
.audit-wrap{--lsg-radius:5px}
.audit-card{border:1px solid rgba(120,120,120,.18);border-radius:0;background:linear-gradient(180deg,rgba(255,255,255,.98),rgba(248,249,252,.96));box-shadow:0 8px 24px rgba(20,30,50,.06);padding:16px;margin-bottom:16px}
.audit-grid{display:grid;gap:16px}
.audit-grid-4{grid-template-columns:repeat(4,minmax(0,1fr))}
.audit-layout{display:grid;grid-template-columns:280px 1fr;gap:16px}
.audit-metric{font-size:28px;font-weight:800;line-height:1}
.audit-muted{color:#6c757d}
.prm-dashboard-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.prm-dashboard-metric{position:relative;overflow:hidden;border-radius:0;padding:16px;min-height:104px;border:1px solid rgba(148,163,184,.25);background:linear-gradient(135deg,rgba(255,255,255,.96),rgba(248,250,252,.86));box-shadow:0 8px 24px rgba(15,23,42,.08);display:flex;justify-content:space-between;gap:14px;align-items:center}
.prm-dashboard-metric__label{font-size:12px;text-transform:uppercase;color:#64748b;font-weight:800;letter-spacing:.04em}
.prm-dashboard-metric__value{font-size:30px;line-height:1;font-weight:900;color:#0f172a;margin-top:6px}
.prm-dashboard-metric__icon{width:46px;height:46px;border-radius:0;display:flex;align-items:center;justify-content:center;background:color-mix(in srgb,var(--metric-color,#2563eb) 16%,transparent);color:var(--metric-color,#2563eb);font-size:20px;border:1px solid color-mix(in srgb,var(--metric-color,#2563eb) 28%,transparent);flex:0 0 46px}
.prm-dashboard-metric.roles{--metric-color:#2563eb}
.prm-dashboard-metric.permissions{--metric-color:#7c3aed}
.prm-dashboard-metric.critical{--metric-color:#dc2626}
.prm-dashboard-metric.users{--metric-color:#16a34a}
.audit-badge{display:inline-flex;align-items:center;gap:6px;border-radius:5px;padding:4px 8px;font-size:12px;font-weight:700;text-transform:uppercase}
.audit-badge.info,.audit-badge.debug{background:#eef4ff;color:#2452a3}
.audit-badge.notice{background:#f3f0ff;color:#5138a6}
.audit-badge.warning{background:#fff6dc;color:#9a6400}
.audit-badge.error{background:#ffe9e9;color:#b32020}
.audit-badge.critical,.audit-badge.security{background:#2c0b0e;color:#fff}
.audit-table{width:100%;border-collapse:separate;border-spacing:0 8px}
.audit-table tr{background:#fff;box-shadow:0 4px 14px rgba(20,30,50,.04)}
.audit-table td,.audit-table th{padding:10px 12px;vertical-align:middle}
.audit-filter label{font-size:12px;font-weight:700;color:#6c757d;text-transform:uppercase}
.audit-filter input,.audit-filter select{width:100%;border:1px solid rgba(120,120,120,.25);border-radius:5px;padding:8px 10px}
.audit-json{background:#111827;color:#d1d5db;border-radius:5px;padding:12px;white-space:pre-wrap;overflow:auto;max-height:420px}
.audit-diff{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.audit-old{background:#fff0f0}
.audit-new{background:#effaf1}
.audit-timeline-item{border-left:3px solid #d0d7e2;padding-left:14px;margin-bottom:18px;position:relative}
.audit-timeline-item:before{content:'';width:11px;height:11px;border-radius:50%;background:#6c757d;position:absolute;left:-7px;top:6px}
@media(max-width:992px){.audit-grid-4,.audit-layout,.audit-diff,.prm-dashboard-grid{grid-template-columns:1fr}}
</style>
