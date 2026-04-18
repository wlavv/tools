<style>
.pm-header{display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;margin-bottom:1rem;flex-wrap:wrap}
.pm-title h1{margin:0;font-size:1.85rem;font-weight:800;letter-spacing:-.02em;line-height:1.05;color:var(--text-primary,#f8fafc)}
.pm-subtitle{opacity:.72;font-size:.78rem;text-transform:uppercase;letter-spacing:.08em;font-weight:700;color:var(--text-muted,#94a3b8)}
.pm-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1rem}
.pm-stat{border:1px solid rgba(255,255,255,.08);border-radius:5px;padding:1rem 1.1rem;background:linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);box-shadow:0 8px 20px rgba(0,0,0,.14);transition:border-color .2s ease,transform .2s ease,box-shadow .2s ease}
.pm-stat:hover{transform:translateY(-1px);border-color:rgba(96,165,250,.18);box-shadow:0 12px 26px rgba(0,0,0,.18)}
.pm-groups{display:flex;flex-direction:column;gap:1.15rem}
.pm-group{border:1px solid rgba(255,255,255,.07);border-radius:5px;background:linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);box-shadow:0 10px 24px rgba(0,0,0,.16);overflow:hidden}
.pm-group-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.1rem 1.25rem;cursor:pointer;background:rgba(255,255,255,.02)}
.pm-group-left{display:flex;align-items:center;gap:1rem}
.pm-group-plus{font-size:1.25rem;font-weight:800;color:#22c55e;width:26px;line-height:1;display:flex;align-items:center;justify-content:center}
.pm-group-title{font-size:1.05rem;font-weight:800;line-height:1.2;color:var(--text-primary,#f8fafc)}
.pm-group-meta{display:flex;gap:1rem;flex-wrap:wrap;opacity:.84;font-size:.84rem;margin-top:.2rem;color:var(--text-muted,#94a3b8)}
.pm-group-body{display:none;padding:1rem 0 1.2rem 0;border-top:1px solid rgba(255,255,255,.06)}
.pm-group.is-open .pm-group-body{display:block}
.pm-group-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(206px,206px));gap:1rem;padding:0 1rem;align-items:start}
.pm-project-card{width:206px;border-radius:5px;border:1px solid rgba(255,255,255,.08);background:linear-gradient(180deg,rgba(41,50,62,.96) 0%,rgba(33,41,52,.98) 100%);box-shadow:0 10px 22px rgba(0,0,0,.16);overflow:hidden;transition:border-color .2s ease,transform .2s ease,box-shadow .2s ease}
.pm-project-card:hover{transform:translateY(-2px);border-color:rgba(96,165,250,.18);box-shadow:0 14px 28px rgba(0,0,0,.2)}
.pm-project-inner{padding:.9rem 0 0 0}
.pm-project-title{text-align:center;font-size:.98rem;font-weight:800;line-height:1.25;min-height:2.4rem;margin-bottom:.7rem;color:var(--text-primary,#f8fafc);padding:0 .65rem}
.pm-project-logo-wrap{display:flex;justify-content:center;margin-bottom:.8rem}
.pm-project-logo{width:118px;height:118px;border-radius:5px;background:rgba(255,255,255,.96);display:flex;align-items:center;justify-content:center;overflow:hidden;border:1px solid rgba(15,23,42,.08)}
.pm-project-logo img{max-width:100%;max-height:100%;object-fit:contain}
.pm-project-logo span{font-size:.78rem;color:#475569;text-align:center;padding:.5rem}
.pm-project-actions{display:grid;grid-template-columns:repeat(5,1fr);gap:6px;margin:6px 6px 8px}
.pm-btn-mini{height:36px;width: 36px; margin: 3px; border-radius:5px;border:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;text-decoration:none;color:#e2e8f0;background:rgba(255,255,255,.04);padding:0;transition:all .2s ease}
.pm-btn-mini:hover{transform:translateY(-1px);color:#fff;border-color:rgba(255,255,255,.14)}
.pm-btn-danger{background:rgba(239,68,68,.12);border-color:rgba(239,68,68,.2);color:#fca5a5}
.pm-btn-danger:hover{background:rgba(239,68,68,.18);color:#fff}
.pm-btn-warn{background:rgba(245,158,11,.12);border-color:rgba(245,158,11,.18);color:#fcd34d}
.pm-btn-warn:hover{background:rgba(245,158,11,.18);color:#fff}
.pm-btn-info{background:rgba(59,130,246,.12);border-color:rgba(59,130,246,.2);color:#93c5fd}
.pm-btn-info:hover{background:rgba(59,130,246,.18);color:#fff}
.pm-btn-success{background:rgba(34,197,94,.12);border-color:rgba(34,197,94,.18);color:#86efac}
.pm-btn-success:hover{background:rgba(34,197,94,.18);color:#fff}
.pm-btn-primaryx{background:rgba(37,99,235,.14);border-color:rgba(37,99,235,.22);color:#bfdbfe}
.pm-btn-primaryx:hover{background:rgba(37,99,235,.2);color:#fff}
.pm-status-bar{height:30px;display:flex;align-items:center;justify-content:center;font-size:.76rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;border-top:1px solid rgba(255,255,255,.06)}
.pm-status-in-progress{background:linear-gradient(180deg,#3b82f6,#2563eb);color:#fff}
.pm-status-new{background:linear-gradient(180deg,#8b5cf6,#7c3aed);color:#fff}
.pm-status-hold{background:linear-gradient(180deg,#94a3b8,#64748b);color:#fff}
.pm-status-done{background:linear-gradient(180deg,#22c55e,#16a34a);color:#fff}
.pm-status-default{background:linear-gradient(180deg,#64748b,#475569);color:#fff}
.pm-card{border:1px solid rgba(255,255,255,.08);border-radius:5px;padding:1rem;background:linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);box-shadow:0 8px 20px rgba(0,0,0,.14);margin-bottom:1rem}
.pm-grid{display:grid;gap:1rem}
.pm-grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}
.pm-info-box{padding:1rem}
.pm-tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem}
.pm-tab{border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.03);padding:.55rem .9rem;border-radius:5px;color:var(--text-primary,#e2e8f0);font-size:.82rem;font-weight:700;transition:all .2s ease}
.pm-tab:hover{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.14)}
.pm-tab.is-active{background:rgba(37,99,235,.14);border-color:rgba(59,130,246,.2);color:#dbeafe;box-shadow:inset 0 0 0 1px rgba(59,130,246,.08)}
.pm-tab-panel{display:none}
.pm-tab-panel.is-active{display:block}
.pm-empty{padding:1rem;border:1px dashed rgba(255,255,255,.12);border-radius:5px;background:rgba(255,255,255,.02);color:var(--text-muted,#94a3b8)}

body.theme-light .pm-title h1,body[data-theme="light"] .pm-title h1{color:#18212b}
body.theme-light .pm-subtitle,body[data-theme="light"] .pm-subtitle{color:#64748b;opacity:1}
body.theme-light .pm-stat,body.theme-light .pm-group,body.theme-light .pm-project-card,body.theme-light .pm-card,body[data-theme="light"] .pm-stat,body[data-theme="light"] .pm-group,body[data-theme="light"] .pm-project-card,body[data-theme="light"] .pm-card{background:linear-gradient(180deg,rgba(255,255,255,.98) 0%,rgba(247,249,252,.98) 100%);border:1px solid rgba(21,32,51,.1);box-shadow:0 8px 20px rgba(15,23,42,.06)}
body.theme-light .pm-group-head,body[data-theme="light"] .pm-group-head{background:rgba(15,23,42,.015)}
body.theme-light .pm-group-title,body.theme-light .pm-project-title,body[data-theme="light"] .pm-group-title,body[data-theme="light"] .pm-project-title{color:#18212b}
body.theme-light .pm-group-meta,body.theme-light .pm-empty,body[data-theme="light"] .pm-group-meta,body[data-theme="light"] .pm-empty{color:#64748b}
body.theme-light .pm-group-body,body[data-theme="light"] .pm-group-body{border-top:1px solid rgba(21,32,51,.06)}
body.theme-light .pm-btn-mini,body[data-theme="light"] .pm-btn-mini{background:rgba(15,23,42,.025);border-color:rgba(21,32,51,.08);color:#334155; width: 36px; height: 36px;margin: 3px;}
body.theme-light .pm-btn-mini:hover,body[data-theme="light"] .pm-btn-mini:hover{border-color:rgba(37,99,235,.18);color:#18212b; width: 36px; height: 36px;margin: 3px;}
body.theme-light .pm-tab,body[data-theme="light"] .pm-tab{background:rgba(15,23,42,.025);border-color:rgba(21,32,51,.08);color:#334155}
body.theme-light .pm-tab:hover,body[data-theme="light"] .pm-tab:hover{background:rgba(15,23,42,.04)}
body.theme-light .pm-tab.is-active,body[data-theme="light"] .pm-tab.is-active{background:rgba(37,99,235,.08);border-color:rgba(37,99,235,.14);color:#1d4ed8}
body.theme-light .pm-empty,body[data-theme="light"] .pm-empty{background:rgba(15,23,42,.015);border-color:rgba(21,32,51,.1)}

@media (max-width:992px){
.pm-stats,.pm-grid-2{grid-template-columns:1fr}
.pm-group-head{align-items:flex-start;flex-direction:column}
}
@media (max-width:640px){
.pm-group-cards{grid-template-columns:1fr;padding:0 .75rem}
.pm-project-card{width:100%}
.pm-project-actions{grid-template-columns:repeat(5,1fr)}
}
</style>