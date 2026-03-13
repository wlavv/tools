<style>
.pm-header{display:flex;justify-content:space-between;align-items:flex-end;gap:1rem;margin-bottom:1rem;flex-wrap:wrap}
.pm-title h1{margin:0;font-size:1.9rem;font-weight:800;letter-spacing:.03em}
.pm-subtitle{opacity:.72;font-size:.88rem;text-transform:uppercase}
.pm-stats{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:1rem;margin-bottom:1rem}
.pm-stat{border:1px solid rgba(255,255,255,.07);border-radius:20px;padding:1rem 1.1rem;background: linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);box-shadow:0 14px 28px rgba(0,0,0,.18)}
.pm-groups{display:flex;flex-direction:column;gap:1.15rem}
.pm-group{border:1px solid rgba(255,255,255,.06);border-radius:18px;background: linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);box-shadow:0 14px 30px rgba(0,0,0,.22);overflow:hidden}
.pm-group-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1.15rem 1.3rem;cursor:pointer}
.pm-group-left{display:flex;align-items:center;gap:1rem}
.pm-group-plus{font-size:1.55rem;font-weight:700;color:#00d11f;width:30px;line-height:1}
.pm-group-title{font-size:1.18rem;font-weight:800}
.pm-group-meta{display:flex;gap:1rem;flex-wrap:wrap;opacity:.8;font-size:.92rem;margin-top:.15rem}
.pm-group-body{display:none;padding:1rem 0 1.2rem 0;border-top:1px solid rgba(255,255,255,.05)}
.pm-group.is-open .pm-group-body{display:block; background: rgba( 10, 10, 10, 0.4);}
.pm-group-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(206px,206px));gap:1rem;padding:0 1rem;align-items:start}
.pm-project-card{width:206px;border-radius:18px;background:linear-gradient(180deg,rgba(41,58,84,.95),rgba(29,42,60,.92));border:1px solid rgba(255,255,255,.07);box-shadow:0 16px 24px rgba(0,0,0,.24);overflow:hidden}
.pm-project-inner{padding:.85rem 0 0 0}
.pm-project-title{text-align:center;font-size:1.02rem;font-weight:800;line-height:1.2;min-height:2.2rem;margin-bottom:.6rem}
.pm-project-logo-wrap{display:flex;justify-content:center;margin-bottom:.75rem}
.pm-project-logo{width:118px;height:118px;border-radius:12px;background:#f2f2f2;display:flex;align-items:center;justify-content:center;overflow:hidden}
.pm-project-logo img{max-width:100%;max-height:100%;object-fit:contain;}
.pm-project-logo span{font-size:.82rem;color:#434343;text-align:center;padding:.5rem}
.pm-project-actions{display:grid;grid-template-columns:48px 48px 48px 48px 48px;gap:4px;margin: 5px 2px}
.pm-btn-mini{width: 90%;height:36px;border-radius:11px;border:1px solid rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;text-decoration:none;color:#fff;background:rgba(255,255,255,.06);padding:0}
.pm-btn-danger{background:linear-gradient(180deg,rgba(128,82,96,.9),rgba(99,61,72,.9))}
.pm-btn-warn{background:linear-gradient(180deg,rgba(111,103,83,.92),rgba(93,84,62,.92))}
.pm-btn-info{background:linear-gradient(180deg,rgba(67,80,124,.95),rgba(56,67,103,.95))}
.pm-btn-success{background:linear-gradient(180deg,rgba(45,99,92,.95),rgba(35,80,74,.95))}
.pm-btn-primaryx{background:linear-gradient(180deg,rgba(42,112,184,.95),rgba(33,87,145,.95))}
.pm-status-bar{height:29px;display:flex;align-items:center;justify-content:center;font-size:.83rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em;border-top:1px solid rgba(255,255,255,.06)}
.pm-status-in-progress{background:linear-gradient(180deg,#36a2ff,#2388e1);color:#fff}
.pm-status-new{background:linear-gradient(180deg,#a500b9,#8a00a3);color:#fff}
.pm-status-hold{background:linear-gradient(180deg,#979797,#858585);color:#fff}
.pm-status-done{background:linear-gradient(180deg,#2fca70,#22b45e);color:#fff}
.pm-status-default{background:linear-gradient(180deg,#5d6a7b,#4f5b6a);color:#fff}
.pm-card{border:1px solid rgba(255,255,255,.07);border-radius:18px;padding:1rem;background: linear-gradient(180deg,rgba(37,47,59,.94) 0%,rgba(32,40,51,.96) 100%);margin-bottom:1rem}
.pm-grid{display:grid;gap:1rem}
.pm-grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}
.pm-info-box{padding:1rem;}
.pm-tabs{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem}
.pm-tab{border:1px solid rgba(255,255,255,.12);background:transparent;padding:.55rem .9rem;border-radius:999px;color:inherit}
.pm-tab.is-active{outline:2px solid rgba(45,156,255,.35)}
.pm-tab-panel{display:none}
.pm-tab-panel.is-active{display:block}
.pm-empty{padding:1rem;border:1px dashed rgba(255,255,255,.12);border-radius:18px}
@media (max-width:992px){.pm-stats,.pm-grid-2{grid-template-columns:1fr}.pm-group-head{align-items:flex-start;flex-direction:column}}
@media (max-width:640px){.pm-group-cards{grid-template-columns:1fr;padding:0 .75rem}.pm-project-card{width:100%}.pm-project-actions{grid-template-columns:repeat(5,1fr)}}
</style>
