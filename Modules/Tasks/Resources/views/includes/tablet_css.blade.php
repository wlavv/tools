<style>
.family-planner-tablet{
    --fp-bg-1:#eef2f6;
    --fp-bg-2:#e7ecf2;
    --fp-text:#27313d;
    --fp-text-soft:#6f7a88;
    --fp-panel:rgba(255,255,255,.36);
    --fp-panel-strong:rgba(255,255,255,.50);
    --fp-border:rgba(255,255,255,.46);
    --fp-border-soft:rgba(255,255,255,.36);
    --fp-shadow:0 10px 30px rgba(15,23,42,.08);
    --fp-shadow-soft:0 4px 14px rgba(15,23,42,.06);
    --fp-blue:#89b6df;
    --fp-gold:#d6b16b;
    --fp-green:#8bc39d;
    --fp-radius:5px;
    --fp-gap:14px;
}

*{box-sizing:border-box}

.family-planner-tablet{
    margin:0;
    width:100%;
    height:100%;
    overflow:hidden;
    font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","Segoe UI",Roboto,Helvetica,Arial,sans-serif;
    background:
        radial-gradient(circle at top left, rgba(214,177,107,.10), transparent 28%),
        radial-gradient(circle at top right, rgba(137,182,223,.12), transparent 28%),
        linear-gradient(180deg, var(--fp-bg-1) 0%, var(--fp-bg-2) 100%);
    color:var(--fp-text);
}

.family-planner-tablet{
    overflow:hidden;
}

.fp-app{
    width:100vw;
    height:100vh;
    padding:12px;
    overflow:hidden;
}

.fp-layout{
    width:100%;
    height:100%;
    display:grid;
    grid-template-columns:60% 40%;
    gap:var(--fp-gap);
    overflow:hidden;
}

.fp-left,.fp-right{
    min-width:0;
    height:100%;
    display:flex;
    flex-direction:column;
    gap:var(--fp-gap);
    overflow:hidden;
}

.fp-panel{
    position:relative;
    background:linear-gradient(180deg,var(--fp-panel-strong) 0%, var(--fp-panel) 100%);
    border:1px solid var(--fp-border);
    border-radius:var(--fp-radius);
    box-shadow:var(--fp-shadow), inset 0 1px 0 rgba(255,255,255,.52);
    backdrop-filter:blur(18px) saturate(120%);
    -webkit-backdrop-filter:blur(18px) saturate(120%);
    overflow:hidden;
}

.fp-header-panel{
    padding:18px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    width:100%;
    flex:0 0 auto;
}

.fp-header-copy{
    display:flex;
    flex-direction:column;
    justify-content:center;
    min-width:0;
    white-space:nowrap;
}

.fp-time{
    font-size:4.4rem;
    line-height:.92;
    font-weight:760;
    letter-spacing:-.06em;
}

.fp-date-line{
    margin-top:6px;
    font-size:1.18rem;
    color:var(--fp-text-soft);
    font-weight:550;
    text-transform:capitalize;
}

.fp-weather-inline{
    display:flex;
    align-items:center;
    justify-content:flex-end;
    flex:1;
    min-width:0;
}

.fp-weather-now{
    display:flex;
    align-items:center;
    flex-wrap:nowrap;
    width:100%;
    justify-content:flex-end;
}

.fp-weather-now img{
    width:120px;
    height:120px;
    flex:0 0 auto;
    object-fit:contain;
    display:block;
}

.fp-weather-now-copy{
    display:flex;
    flex-direction:column;
    justify-content:center;
    white-space:nowrap;
}

.fp-weather-temp{
    font-size:1.85rem;
    line-height:1;
    font-weight:740;
}

.fp-weather-label{
    margin-top:4px;
    font-size:.98rem;
    font-weight:620;
}

.fp-weather-minmax{
    margin-top:4px;
    font-size:.84rem;
    color:var(--fp-text-soft);
    font-weight:600;
}

.fp-weather-thumbs{
    display:flex;
    align-items:center;
    gap:10px;
    margin-left:16px;
    flex:0 0 auto;
}

.fp-weather-thumb{
    padding:8px 6px;
    text-align:center;
    border-radius:var(--fp-radius);
    background:rgba(255,255,255,.22);
    border:1px solid var(--fp-border-soft);
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    min-width:48px;
}

.fp-weather-thumb-time{
    font-size:.78rem;
    color:var(--fp-text-soft);
    font-weight:700;
    margin-bottom:4px;
}

.fp-weather-thumb img{
    width:26px;
    height:26px;
    object-fit:contain;
    display:block;
    margin:0 auto 4px;
}

.fp-weather-thumb-temp{
    font-size:.92rem;
    font-weight:720;
}

.fp-calendar-panel{
    flex:0 0 auto;
    padding:14px;
}

.fp-calendar-header{
    display:grid;
    grid-template-columns:34px 1fr 34px;
    gap:10px;
    align-items:center;
    margin-bottom:12px;
}

.fp-calendar-header h2{
    margin:0;
    text-align:center;
    font-size:1.28rem;
    font-weight:720;
    text-transform:capitalize;
}

.fp-nav-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    width:34px;
    height:34px;
    border-radius:var(--fp-radius);
    background:rgba(255,255,255,.24);
    border:1px solid var(--fp-border-soft);
    color:var(--fp-text-soft);
    text-decoration:none;
}

.fp-calendar-grid{
    display:grid;
    grid-template-columns:repeat(7, minmax(0,1fr));
    gap:6px;
}

.fp-weekday{
    text-align:center;
    font-size:.72rem;
    font-weight:700;
    color:var(--fp-text-soft);
    text-transform:uppercase;
}

.fp-day{
    position:relative;
    width:100%;
    min-height:42px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:var(--fp-radius);
    background:rgba(255,255,255,.22);
    border:1px solid transparent;
    color:var(--fp-text);
    text-decoration:none;
    font-size:.95rem;
    font-weight:680;
}

.fp-day-dots{
    position:absolute;
    left:50%;
    bottom:0px;
    transform:translateX(-50%);
    display:flex;
    align-items:center;
    justify-content:center;
    gap:4px;
    max-width:calc(100% - 10px);
    line-height:0;
    pointer-events:none;
}

.fp-day-dot{
    width:12px;
    height:12px;
    min-width:12px;
    min-height:12px;
    max-width:12px;
    max-height:12px;
    flex:0 0 12px;
    aspect-ratio:1 / 1;
    border-radius:50%;
    display:block;
    box-sizing:border-box;
    line-height:0;
    box-shadow:0 0 0 1px rgba(255,255,255,.72);
}

.fp-day.is-muted{opacity:.42}
.fp-day.is-today{border-color:rgba(137,182,223,.52)}
.fp-day.is-selected{
    background:rgba(214,177,107,.88);
    color:#fff;
    border-color:rgba(214,177,107,.88);
}

.fp-events-panel{
    flex:1 1 auto;
    padding:14px;
    min-height:0;
    display:flex;
    flex-direction:column;
}

.fp-section-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:10px;
    margin-bottom:12px;
    flex:0 0 auto;
}

.fp-section-head h3{
    margin:0;
    font-size:1.32rem;
    font-weight:720;
}

.fp-section-head p{
    margin:4px 0 0;
    color:var(--fp-text-soft);
    font-size:.92rem;
    font-weight:600;
}

.fp-action-btn,.fp-clear-member{
    display:inline-flex;
    align-items:center;
    gap:8px;
    min-height:38px;
    padding:0 12px;
    border-radius:var(--fp-radius);
    background:rgba(255,255,255,.22);
    border:1px solid var(--fp-border-soft);
    color:var(--fp-text);
    text-decoration:none;
    font-size:.9rem;
    font-weight:650;
}

.fp-events-list{
    min-height:0;
    overflow-y:auto;
    overflow-x:hidden;
    padding-right:4px;
}

.fp-events-list::-webkit-scrollbar,
.fp-task-list::-webkit-scrollbar{
    width:6px;
}

.fp-events-list::-webkit-scrollbar-thumb,
.fp-task-list::-webkit-scrollbar-thumb{
    background:rgba(111,122,136,.28);
    border-radius:999px;
}

.fp-event-row{
    display:grid;
    grid-template-columns:76px minmax(0,1fr);
    gap:12px;
    align-items:flex-start;
    padding:10px 12px;
    margin-bottom:8px;
    border-radius:var(--fp-radius);
    background:rgba(255,255,255,.22);
    border:1px solid var(--fp-border-soft);
}

.fp-event-time{
    font-size:1rem;
    font-weight:730;
    color:var(--fp-text-soft);
}

.fp-event-title{
    font-size:1.03rem;
    font-weight:720;
    line-height:1.15;
}

.fp-event-meta{
    margin-top:4px;
    font-size:.9rem;
    line-height:1.3;
    color:var(--fp-text-soft);
}

.fp-members-panel{
    padding:5px;
    flex:0 0 auto;
}

.fp-members-grid{
    display:grid;
    grid-template-columns:repeat(4, minmax(0,1fr));
    gap:12px;
}

.fp-member-card{
    padding:10px 8px;
    text-decoration:none;
    color:inherit;
    border-radius:var(--fp-radius);
    background:rgba(255,255,255,.22);
    border:1px solid var(--fp-border-soft);
    text-align:center;
    min-width:0;
}

.fp-member-card.is-active{
    border-color:rgba(214,177,107,.64);
    box-shadow:0 8px 22px rgba(214,177,107,.10);
    background:linear-gradient(180deg, rgba(255,255,255,.42), rgba(214,177,107,.10));
}

.fp-member-photo-wrap{
    width:110px;
    height:110px;
    margin:0 auto 10px;
    padding:4px;
    border-radius:var(--fp-radius);
    background:rgba(255,255,255,.30);
    border:1px solid rgba(255,255,255,.54);
    box-shadow:var(--fp-shadow-soft);
}

.fp-member-photo-wrap img{
    width:100%;
    height:100%;
    object-fit:cover;
    display:block;
    border-radius:var(--fp-radius);
    background:#edf1f6;
}

.fp-member-name{
    font-size:1.08rem;
    font-weight:720;
    line-height:1.15;
}

.fp-member-counters{
    margin-top:8px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    flex-wrap:wrap;
}

.fp-member-counters span{
    display:inline-flex;
    align-items:center;
    min-height:26px;
    padding:0 8px;
    border-radius:var(--fp-radius);
    background:rgba(255,255,255,.30);
    border:1px solid var(--fp-border-soft);
    font-size:.78rem;
    font-weight:700;
    color:var(--fp-text-soft);
}

.fp-main-panel{
    flex:1 1 auto;
    min-height:0;
    padding:16px;
    display:flex;
    flex-direction:column;
    overflow:hidden;
}

.fp-thought-wrap{
    height:100%;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
    width:100%;
}

.fp-thought-kicker{
    align-items:center;
    min-height:30px;
    padding:0 10px;
    border-radius:var(--fp-radius);
    background:rgba(255,255,255,.26);
    border:1px solid var(--fp-border-soft);
    color:var(--fp-text-soft);
    font-size:.78rem;
    font-weight:700;
    letter-spacing:.04em;
    text-transform:uppercase;
    text-align:center;
    width:100%;
}

.fp-thought-quote{
    margin:8px 0;
    font-size:2rem;
    line-height:1.18;
    font-weight:650;
    letter-spacing:-.03em;
    max-width:92%;
    text-align:center;
    width:100%;
}

.fp-thought-author{
    margin-top:14px;
    color:var(--fp-text-soft);
    font-size:1rem;
    font-weight:600;
    text-align:center;
    width:100%;
}

.fp-task-list{
    flex:1 1 auto;
    min-height:0;
    max-height:100%;
    display:flex;
    flex-direction:column;
    gap:10px;
    overflow-y:auto;
    overflow-x:hidden;
    padding-right:4px;
    -webkit-overflow-scrolling:touch;
    overscroll-behavior:contain;
    width:100%;
    min-width:0;
}

.fp-task-row{
    display:flex !important;
    align-items:center;
    gap:10px;
    width:100%;
    min-width:0;
    flex:0 0 auto;
}

.fp-task-checkbox{
    position:absolute;
    opacity:0;
    pointer-events:none;
    flex:0 0 22px !important;
}

.fp-task-checkmark{
    width:28px;
    height:28px;
    border-radius:var(--fp-radius);
    border:2px solid var(--fp-blue);
    display:flex;
    align-items:center;
    justify-content:center;
    background:rgba(255,255,255,.46);
    color:transparent;
    flex:0 0 22px !important;
}

.fp-task-checkbox:checked + .fp-task-checkmark{
    background:var(--fp-green);
    border-color:var(--fp-green);
    color:#fff;
}

.fp-task-image{
    flex:0 0 50px !important;
    width:50px !important;
    height:50px !important;
    border-radius:5px;
    overflow:hidden;
}

.fp-task-image img{
    width:100%;
    height:100%;
    object-fit:cover;
}

.fp-task-title{
    flex:1 1 auto !important;
    width:auto !important;
    max-width:none !important;
    min-width:0 !important;
    display:block !important;
    white-space:nowrap !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
    font-size:1.08rem;
    font-weight:650;
    line-height:1.2;
}

.fp-task-title.is-done{
    color:var(--fp-text-soft);
    text-decoration:line-through;
}

.fp-task-actions{
    display:flex;
    gap:12px;
    margin-left:auto;
    flex:0 0 auto;
}

.fp-task-action{
    display:flex;
    align-items:center;
    justify-content:center;
    width:48px;
    height:48px;
    font-size:28px;
    line-height:1;
    cursor:pointer;
    user-select:none;
    transition:transform 0.12s ease, opacity 0.12s ease;
}

.fp-task-action:active{transform:scale(0.9)}
.fp-task-action.is-ok{filter:drop-shadow(0 2px 6px rgba(79,155,114,.35))}
.fp-task-action.is-not-ok{filter:drop-shadow(0 2px 6px rgba(201,111,111,.35))}

.fp-empty-state{
    height:100%;
    min-height:120px;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
    color:var(--fp-text-soft);
    font-size:1rem;
    font-weight:600;
    border:1px dashed var(--fp-border-soft);
    border-radius:var(--fp-radius);
    background:rgba(255,255,255,.16);
}

.fp-fab-event{
    position:fixed;
    right:16px;
    bottom:16px;
    z-index:1060;
    width:58px;
    height:58px;
    border:0;
    border-radius:999px;
    display:none;
    align-items:center;
    justify-content:center;
    background:linear-gradient(180deg, rgba(214,177,107,.96), rgba(195,155,75,.96));
    color:#fff;
    box-shadow:0 18px 30px rgba(125,92,31,.28), inset 0 1px 0 rgba(255,255,255,.35);
}

.fp-fab-event i{font-size:1.15rem}

.fp-modal-content,
.modal-content{border-radius:var(--fp-radius)}

.fp-modal-content{
    background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(248,250,252,.92));
    border:1px solid rgba(255,255,255,.7);
    box-shadow:0 30px 80px rgba(15,23,42,.18);
    backdrop-filter:blur(18px) saturate(140%);
    -webkit-backdrop-filter:blur(18px) saturate(140%);
}

.fp-modal-content .modal-header,
.fp-modal-content .modal-footer{
    border-color:rgba(111,122,136,.12);
}

.fp-modal-content .modal-title{
    font-weight:750;
    letter-spacing:-.02em;
}

.fp-modal-content .form-label{
    font-size:.86rem;
    font-weight:700;
    color:var(--fp-text-soft);
}

.fp-modal-content .form-control,
.fp-modal-content .form-select{
    min-height:48px;
    background:rgba(255,255,255,.78);
    border:1px solid rgba(111,122,136,.18);
    color:var(--fp-text);
}

.fp-modal-content textarea.form-control{min-height:104px}
.fp-modal-content .btn-primary{
    background:linear-gradient(180deg, rgba(214,177,107,.96), rgba(195,155,75,.96));
    border-color:rgba(195,155,75,.96);
}

.form-control,.form-select,.btn,.btn-close{
    border-radius:var(--fp-radius) !important;
}

@media (max-width: 1180px){
    .fp-app{padding:10px}
    .fp-layout{gap:10px}
    .fp-header-panel{padding:14px}
    .fp-time{font-size:3.7rem}
    .fp-member-photo-wrap{width:92px;height:92px}
    .fp-thought-quote{font-size:1.68rem}
}

@media (orientation: portrait) and (min-width: 768px) and (max-width: 1180px){
    .family-planner-tablet{
        overflow:hidden;
    }

    .fp-app{
        padding:10px;
        height:100vh;
    }

    .fp-layout{
        grid-template-columns:1fr;
        grid-template-rows:52% 48%;
        gap:10px;
        height:100%;
    }

    .fp-left,
    .fp-right{
        min-height:0;
        height:100%;
        overflow:hidden;
        gap:10px;
    }

    .fp-left{
        display:grid;
        grid-template-rows:auto auto minmax(0,1fr);
    }

    .fp-right{
        display:grid;
        grid-template-rows:auto minmax(0,1fr);
    }

    .fp-header-panel{
        padding:14px;
        align-items:flex-start;
        gap:14px;
    }

    .fp-time{font-size:3.15rem}
    .fp-date-line{font-size:1rem}

    .fp-weather-inline{
        min-width:0;
        width:auto;
    }

    .fp-weather-now{
        justify-content:flex-end;
        gap:10px;
    }

    .fp-weather-now img{
        width:72px;
        height:72px;
    }

    .fp-weather-temp{font-size:1.45rem}
    .fp-weather-label{font-size:.9rem}
    .fp-weather-minmax{font-size:.76rem}

    .fp-weather-thumbs{
        margin-left:8px;
        gap:6px;
    }

    .fp-weather-thumb{
        min-width:42px;
        padding:6px 4px;
    }

    .fp-weather-thumb-time{font-size:.68rem}

    .fp-weather-thumb img{
        width:22px;
        height:22px;
    }

    .fp-weather-thumb-temp{font-size:.82rem}

    .fp-calendar-panel{padding:12px}
    .fp-calendar-header{margin-bottom:10px}
    .fp-calendar-header h2{font-size:1.1rem}
    .fp-calendar-grid{gap:5px}
    .fp-day{min-height:34px;font-size:.84rem}
    .fp-weekday{font-size:.62rem}

    .fp-members-panel{padding:8px}

    .fp-members-grid{
        grid-template-columns:repeat(4, minmax(0,1fr));
        gap:8px;
    }

    .fp-member-card{padding:8px 6px}

    .fp-member-photo-wrap{
        width:62px;
        height:62px;
        margin-bottom:6px;
    }

    .fp-member-name{font-size:.86rem}

    .fp-member-counters{
        gap:4px;
        margin-top:6px;
    }

    .fp-member-counters span{
        font-size:.64rem;
        min-height:22px;
        padding:0 6px;
    }

    .fp-main-panel,
    .fp-events-panel{
        padding:12px;
        min-height:0;
    }

    .fp-section-head{margin-bottom:10px}
    .fp-section-head h3{font-size:1.08rem}
    .fp-section-head p{font-size:.82rem}

    .fp-event-row{
        grid-template-columns:58px minmax(0,1fr);
        gap:8px;
        padding:8px 10px;
        margin-bottom:6px;
    }

    .fp-event-time{font-size:.86rem}
    .fp-event-title{font-size:.92rem}
    .fp-event-meta{font-size:.78rem}

    .fp-thought-kicker{
        min-height:26px;
        font-size:.68rem;
    }

    .fp-thought-quote{
        font-size:1.28rem;
        margin:10px 0 0;
        max-width:100%;
    }

    .fp-thought-author{
        margin-top:8px;
        font-size:.86rem;
    }

    .fp-task-list{
        gap:8px;
    }

    .fp-task-row{
        gap:8px;
    }

    .fp-task-image{
        flex-basis:42px !important;
        width:42px !important;
        height:42px !important;
    }

    .fp-task-title{
        font-size:.92rem !important;
    }

    .fp-clear-member,
    .fp-action-btn,
    .fp-nav-btn{
        min-height:34px;
    }
}

@media (max-width: 767.98px){
    .family-planner-tablet,.fp-app{
        height:auto;
        min-height:100%;
        overflow:auto;
    }

    .fp-app{
        padding:10px 10px 88px;
    }

    .fp-layout{
        grid-template-columns:1fr;
        gap:10px;
        height:auto;
        overflow:visible;
    }

    .fp-left,.fp-right{
        height:auto;
        overflow:visible;
        gap:10px;
    }

    .fp-header-panel{
        padding:14px;
        flex-direction:column;
        align-items:flex-start;
        gap:12px;
    }

    .fp-time{font-size:2.7rem}
    .fp-date-line{font-size:.98rem}

    .fp-weather-inline,
    .fp-weather-now,
    .fp-weather-now-copy{width:100%}

    .fp-weather-now{
        display:grid;
        grid-template-columns:56px minmax(0,1fr);
        gap:10px;
        align-items:center;
    }

    .fp-weather-now > img{width:56px;height:56px}

    .fp-weather-thumbs{
        grid-column:1 / -1;
        display:grid;
        grid-template-columns:repeat(4, minmax(0,1fr));
        margin-left:0;
        width:100%;
    }

    .fp-calendar-panel,
    .fp-events-panel,
    .fp-main-panel,
    .fp-members-panel{padding:12px}

    .fp-calendar-header h2{font-size:1.08rem}
    .fp-day{min-height:36px;font-size:.88rem}
    .fp-weekday{font-size:.64rem}

    .fp-section-head{
        align-items:center;
        gap:8px;
    }

    .fp-section-head h3{font-size:1.08rem}
    .fp-section-head p{font-size:.82rem}

    .fp-action-btn{display:none}
    .fp-fab-event{display:flex}

    .fp-members-panel{order:-1}

    .fp-members-grid{
        display:flex;
        gap:10px;
        overflow-x:auto;
        padding-bottom:2px;
        scroll-snap-type:x proximity;
    }

    .fp-members-grid::-webkit-scrollbar{display:none}

    .fp-member-card{
        flex:0 0 138px;
        padding:10px;
        scroll-snap-align:start;
    }

    .fp-member-photo-wrap{width:64px;height:64px;margin:0 auto 8px}
    .fp-member-name{font-size:.96rem}
    .fp-member-counters{gap:6px;margin-top:6px}
    .fp-member-counters span{font-size:.7rem;min-height:24px;padding:0 7px}

    .fp-events-list{overflow:visible}
    .fp-task-list{
        overflow-y:auto;
        overflow-x:hidden;
        max-height:420px;
        padding-right:4px;
        -webkit-overflow-scrolling:touch;
    }

    .fp-event-row{
        grid-template-columns:62px minmax(0,1fr);
        gap:10px;
        padding:10px;
    }

    .fp-event-time{font-size:.9rem}
    .fp-event-title{font-size:.96rem}
    .fp-event-meta{font-size:.82rem}
    .fp-thought-wrap{justify-content:flex-start}
    .fp-thought-quote{font-size:1.32rem;max-width:100%;margin-top:12px}
    .fp-thought-author{font-size:.92rem;margin-top:10px}

    .fp-task-row{padding:10px 12px !important}
    .fp-task-image{flex-basis:44px !important;width:44px !important;height:44px !important}
    .fp-task-title{font-size:.98rem !important}

    .fp-clear-member,
    .fp-action-btn,
    .fp-nav-btn{min-height:40px}

    .fp-event-modal-dialog{margin:0}

    .fp-modal-content{
        min-height:100dvh;
        border-radius:0;
    }

    .fp-modal-content .modal-header,
    .fp-modal-content .modal-footer{padding:14px}

    .fp-modal-content .modal-body{padding:14px}
    .fp-modal-content .btn{min-height:46px}
}


/* MOBILE WEATHER FULL WIDTH FIX */

@media (max-width: 767.98px){

    .fp-header-panel{
        flex-direction:column;
        align-items:center;
        text-align:center;
    }

    .fp-header-copy{
        width:100%;
        align-items:center;
    }

    .fp-time{
        text-align:center;
        width:100%;
    }

    .fp-date-line{
        text-align:center;
        width:100%;
    }

    .fp-weather-inline{
        width:100%;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        margin-top:10px;
    }

    .fp-weather-now{
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:6px;
        width:100%;
    }

    .fp-weather-now img{
        width:64px;
        height:64px;
    }

    .fp-weather-now-copy{
        align-items:center;
        text-align:center;
    }

    .fp-weather-temp{
        font-size:1.6rem;
    }

    .fp-weather-label{
        text-align:center;
    }

    .fp-weather-minmax{
        text-align:center;
    }

    .fp-weather-thumbs{
        width:100%;
        display:grid;
        grid-template-columns:repeat(3, 1fr);
        gap:10px;
        margin-top:10px;
    }

    .fp-weather-thumb{
        width:100%;
        align-items:center;
        justify-content:center;
    }

}


@media (orientation: portrait) and (min-width: 768px) and (max-width: 1180px){
    .fp-day-dots{
        bottom:0px;
        gap:3px;
        max-width:calc(100% - 8px);
    }

    .fp-day-dot{
        width:7px;
        height:7px;
        min-width:7px;
        min-height:7px;
        max-width:7px;
        max-height:7px;
        flex-basis:7px;
    }
}

@media (max-width: 767.98px){
    .fp-day-dots{
        bottom:3px;
        gap:3px;
        max-width:calc(100% - 8px);
    }

    .fp-day-dot{
        width:10px;
        height:10px;
        min-width:10px;
        min-height:10px;
        max-width:10px;
        max-height:10px;
        flex-basis:10px;
    }
}

</style>
