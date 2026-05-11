<style>
    .investments-shell{display:flex;flex-direction:column;gap:18px}
    .investments-hero,.investments-card{border:1px solid rgba(148,163,184,.18);border-radius:5px;background:var(--lsg-card-bg,rgba(17,24,39,.88));box-shadow:0 14px 32px rgba(0,0,0,.18)}
    .investments-hero{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:20px 22px}
    .investments-hero__main{display:flex;align-items:center;gap:15px;min-width:0}
    .investments-hero__icon{display:flex;align-items:center;justify-content:center;width:66px;height:66px;flex:0 0 66px;border-radius:5px;background:rgba(34,197,94,.10);border:1px solid rgba(34,197,94,.18);color:#86efac;font-size:26px}
    .investments-eyebrow{margin:0 0 5px;color:#94a3b8;font-size:12px;font-weight:800;text-transform:uppercase}
    .investments-title{margin:0;color:#f8fafc;font-size:28px;line-height:1.12;font-weight:800}
    .investments-subtitle{margin:7px 0 0;color:#cbd5e1;font-size:14px;line-height:1.55}
    .investments-nav{display:flex;flex-wrap:wrap;gap:8px}
    .investments-nav a{display:inline-flex;align-items:center;gap:8px;min-height:38px;padding:8px 11px;border-radius:5px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.66);color:#e2e8f0;text-decoration:none;font-weight:700;font-size:13px}
    .investments-nav a.is-active,.investments-nav a:hover{border-color:rgba(34,197,94,.32);background:rgba(34,197,94,.12);color:#bbf7d0;text-decoration:none}
    .investments-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}
    .investments-stat{padding:16px}
    .investments-stat span{display:block;color:#94a3b8;font-size:12px;font-weight:800;text-transform:uppercase}
    .investments-stat strong{display:block;margin-top:8px;color:#f8fafc;font-size:28px;line-height:1;font-weight:800}
    .investments-card{padding:16px}
    .investments-card__head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
    .investments-card__title{margin:0;color:#f8fafc;font-size:17px;font-weight:800}
    .investments-alert{padding:12px 14px;border-radius:5px;background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.20);color:#bbf7d0;font-weight:700}
    .investments-error{padding:12px 14px;border-radius:5px;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.20);color:#fecaca;font-weight:700}
    .investments-table{width:100%;color:#e5edf7}
    .investments-table th{padding:12px;border-bottom:1px solid rgba(148,163,184,.18);color:#94a3b8;font-size:12px;text-transform:uppercase}
    .investments-table td{padding:12px;border-bottom:1px solid rgba(148,163,184,.10);vertical-align:middle}
    .investments-badge{display:inline-flex;align-items:center;min-height:26px;padding:5px 8px;border-radius:5px;background:rgba(59,130,246,.12);border:1px solid rgba(59,130,246,.20);color:#bfdbfe;font-size:12px;font-weight:800;text-transform:uppercase}
    .investments-badge--success{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.20);color:#86efac}
    .investments-badge--muted{background:rgba(148,163,184,.12);border-color:rgba(148,163,184,.20);color:#cbd5e1}
    .investments-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .investments-form__full{grid-column:1/-1}
    .investments-field label{display:block;margin-bottom:7px;color:#94a3b8;font-size:12px;font-weight:800;text-transform:uppercase}
    .investments-field input,.investments-field select{width:100%;min-height:42px;border-radius:5px;border:1px solid rgba(148,163,184,.18);background:rgba(15,23,42,.66);color:#f8fafc;padding:9px 10px}
    .investments-check{display:flex;align-items:center;gap:8px;color:#e2e8f0;font-weight:700}
    .investments-actions{display:flex;flex-wrap:wrap;justify-content:flex-end;gap:8px}
    .investments-detail-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
    .investments-kv{display:grid;grid-template-columns:150px 1fr;gap:8px;padding:8px 0;border-bottom:1px solid rgba(148,163,184,.10)}
    .investments-kv span{color:#94a3b8;font-weight:800}
    .investments-kv strong{color:#f8fafc}
    .investments-muted{color:#94a3b8}
    body.theme-light .investments-hero,body.theme-light .investments-card,body[data-theme="light"] .investments-hero,body[data-theme="light"] .investments-card{background:#fff;border-color:rgba(15,23,42,.10);box-shadow:0 14px 32px rgba(15,23,42,.08)}
    body.theme-light .investments-title,body.theme-light .investments-card__title,body.theme-light .investments-stat strong,body.theme-light .investments-kv strong,body[data-theme="light"] .investments-title,body[data-theme="light"] .investments-card__title,body[data-theme="light"] .investments-stat strong,body[data-theme="light"] .investments-kv strong{color:#111827}
    body.theme-light .investments-subtitle,body[data-theme="light"] .investments-subtitle{color:#475569}
    body.theme-light .investments-table,body[data-theme="light"] .investments-table{color:#111827}
    body.theme-light .investments-field input,body.theme-light .investments-field select,body[data-theme="light"] .investments-field input,body[data-theme="light"] .investments-field select{background:#f8fafc;color:#111827;border-color:rgba(15,23,42,.12)}
    @media (max-width:768px){.investments-hero{align-items:flex-start;flex-direction:column}.investments-form,.investments-detail-grid{grid-template-columns:1fr}.investments-kv{grid-template-columns:1fr}.investments-table-wrap{overflow-x:auto}}
</style>
