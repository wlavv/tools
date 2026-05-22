@extends('layouts.app')

@section('content')
<div class="idealab-module">
    @yield('idealab-content')
</div>
@endsection

@push('styles')
<style>
    .idealab-module {
        --idealab-radius: 5px;
        --idealab-border: var(--border-soft, rgba(148, 163, 184, .18));
        --idealab-surface: var(--bg-card, var(--card-bg, #1f2937));
        --idealab-surface-soft: var(--bg-card-2, var(--lsg-card-bg-soft, #243142));
        --idealab-text: var(--text-primary, #f8fafc);
        --idealab-muted: var(--text-muted, #94a3b8);
        color: var(--idealab-text);
    }
    .idealab-card {
        border: 1px solid var(--idealab-border) !important;
        border-radius: var(--idealab-radius);
        background: var(--idealab-surface);
        color: var(--idealab-text);
        box-shadow: var(--lsg-card-shadow, none);
    }
    .idealab-card .card-header {
        background: var(--idealab-surface-soft) !important;
        border-color: var(--idealab-border);
        color: var(--idealab-text);
    }
    .idealab-card .card-body,
    .idealab-card .list-group,
    .idealab-card .list-group-item {
        background: transparent;
        color: var(--idealab-text);
    }
    .idealab-card .list-group-item:hover {
        background: var(--idealab-surface-soft);
        color: var(--idealab-text);
    }
    .idealab-soft { background: var(--idealab-surface-soft); }
    .idealab-score { font-weight: 700; font-size: 1.5rem; }
    .idealab-badge { border-radius: var(--idealab-radius); padding: .35rem .55rem; }
    .idealab-layout { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 1rem; align-items: start; }
    .idealab-side { position: sticky; top: 1rem; }
    .idealab-side .list-group-item { border-color: var(--idealab-border); }
    .idealab-summary { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1rem; }
    .idealab-summary-item {
        position: relative;
        overflow: hidden;
        display: grid;
        grid-template-columns: 34px minmax(0, 1fr);
        align-items: center;
        gap: .65rem;
        min-height: 82px;
        border: 1px solid var(--idealab-border);
        border-radius: var(--idealab-radius);
        padding: .85rem .95rem;
        background: var(--idealab-surface);
        color: var(--idealab-text);
        box-shadow: var(--lsg-card-shadow, none);
    }
    .idealab-summary-item__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: 1px solid var(--idealab-border);
        border-radius: var(--idealab-radius);
        background: var(--idealab-surface-soft);
        color: var(--idealab-muted);
        font-size: .95rem;
    }
    .idealab-summary-item:nth-child(1) .idealab-summary-item__icon { color: var(--lsg-bo-btn-success-border, #4ade80); border-color: var(--lsg-bo-btn-success-border, rgba(74, 222, 128, .72)); }
    .idealab-summary-item:nth-child(2) .idealab-summary-item__icon { color: var(--lsg-bo-btn-warning-border, #fbbf24); border-color: var(--lsg-bo-btn-warning-border, rgba(251, 191, 36, .72)); }
    .idealab-summary-item:nth-child(3) .idealab-summary-item__icon { color: var(--lsg-bo-btn-primary-border, #60a5fa); border-color: var(--lsg-bo-btn-primary-border, rgba(96, 165, 250, .76)); }
    .idealab-summary-item:nth-child(4) .idealab-summary-item__icon { color: var(--lsg-bo-btn-primary-border, #60a5fa); border-color: var(--lsg-bo-btn-primary-border, rgba(96, 165, 250, .76)); }
    .idealab-summary-item:nth-child(5) .idealab-summary-item__icon { color: var(--lsg-bo-btn-warning-border, #fbbf24); border-color: var(--lsg-bo-btn-warning-border, rgba(251, 191, 36, .72)); }
    .idealab-summary-item:nth-child(6) .idealab-summary-item__icon { color: var(--lsg-bo-btn-success-border, #4ade80); border-color: var(--lsg-bo-btn-success-border, rgba(74, 222, 128, .72)); }
    .idealab-summary-item span { display: block; color: var(--idealab-muted); font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0; }
    .idealab-summary-item strong { position: relative; z-index: 1; display: block; margin-top: .35rem; font-size: 1rem; line-height: 1.2; word-break: break-word; }
    .idealab-section { scroll-margin-top: 1rem; }
    .idealab-chat-box { max-height: 560px; overflow-y: auto; background: var(--idealab-surface-soft); border: 1px solid var(--idealab-border); border-radius: var(--idealab-radius); padding: .75rem; }
    .idealab-chat-message { border-radius: var(--idealab-radius); padding: .75rem 1rem; margin-bottom: .75rem; background: var(--idealab-surface); border: 1px solid var(--idealab-border); }
    .idealab-chat-message.user { border-left: 4px solid var(--lsg-bo-btn-primary-border, rgba(96, 165, 250, .76)); }
    .idealab-chat-message.assistant { border-left: 4px solid var(--lsg-bo-btn-success-border, rgba(74, 222, 128, .72)); }
    .idealab-chat-message.provider { border-left: 4px solid var(--lsg-bo-btn-warning-border, rgba(251, 191, 36, .72)); }
    .idealab-chat-message.system { border-left: 4px solid var(--idealab-border); }
    .idealab-chat-message.failed { border-left: 4px solid var(--lsg-bo-btn-danger-border, rgba(248, 113, 113, .72)); }
    .idealab-chat-message pre { white-space: pre-wrap; margin: .5rem 0 0; font-size: .84rem; color: var(--idealab-text); }
    .idealab-chat-meta { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .4rem; color: var(--idealab-muted); font-size: .78rem; }
    .idealab-chat-meta span { border: 1px solid var(--idealab-border); border-radius: var(--idealab-radius); padding: .15rem .4rem; background: var(--idealab-surface-soft); }
    .idealab-context-strip { display: flex; flex-wrap: wrap; gap: .5rem; }
    .idealab-context-strip span { display: inline-flex; align-items: center; gap: .45rem; padding: .45rem .65rem; border: 1px solid var(--idealab-border); border-radius: var(--idealab-radius); background: var(--idealab-surface-soft); color: var(--idealab-text); font-size: .85rem; font-weight: 600; }
    .idealab-context-strip i { color: inherit; }
    .idealab-workflow-strip { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: .5rem; }
    .idealab-workflow-step {
        display: flex;
        flex-direction: column;
        border: 1px solid var(--idealab-border);
        border-radius: var(--idealab-radius);
        background: var(--idealab-surface);
        padding: .65rem;
        min-height: 156px;
    }
    .idealab-workflow-step.is-done { border-color: var(--lsg-bo-btn-success-border, rgba(74, 222, 128, .72)); background: var(--idealab-surface-soft); }
    .idealab-workflow-step span { display: inline-flex; width: 24px; height: 24px; align-items: center; justify-content: center; border-radius: var(--idealab-radius); background: var(--idealab-surface-soft); color: var(--idealab-text); font-size: .8rem; font-weight: 700; }
    .idealab-workflow-step.is-done span { background: var(--lsg-bo-btn-success-bg, #166534); color: var(--lsg-bo-btn-success-text, #f0fdf4); }
    .idealab-workflow-step strong { display: block; margin-top: .45rem; font-size: .85rem; }
    .idealab-workflow-step small { display: block; color: var(--idealab-muted); margin-top: .25rem; }
    .idealab-workflow-step-action { margin-top: auto; padding-top: .65rem; }
    .idealab-workflow-step-action .idealab-workflow-action {
        display: flex !important;
        align-items: center;
        justify-content: flex-start;
        width: 100%;
        min-width: 0;
        min-height: 34px;
        padding: .4rem .5rem !important;
        gap: .35rem;
        font-size: .72rem;
        line-height: 1.1;
        text-align: left;
        white-space: nowrap;
    }
    .idealab-workflow-step-action .idealab-workflow-action__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        min-width: 22px;
        height: 22px;
        flex: 0 0 22px;
        background: transparent !important;
        border: 0 !important;
        color: inherit !important;
    }
    .idealab-workflow-step-action .idealab-workflow-action__label {
        display: block !important;
        width: auto !important;
        height: auto !important;
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        line-height: 1.15;
        background: transparent !important;
        border: 0 !important;
        color: inherit !important;
    }
    .idealab-secondary-workflow-actions { border-top: 1px solid var(--idealab-border); padding-top: .75rem; }
    .idealab-issue-list { border: 1px solid var(--lsg-bo-btn-danger-border, rgba(248, 113, 113, .78)); border-radius: var(--idealab-radius); background: var(--idealab-surface-soft); color: var(--idealab-text); }
    .idealab-issue-row { padding: .65rem .75rem; border-bottom: 1px solid var(--idealab-border); }
    .idealab-issue-row:last-child { border-bottom: 0; }
    .idealab-module .border,
    .idealab-module .rounded {
        border-color: var(--idealab-border) !important;
        border-radius: var(--idealab-radius) !important;
        background: var(--idealab-surface-soft);
        color: var(--idealab-text);
    }
    .idealab-module .bg-white,
    .idealab-module .bg-light {
        background: var(--idealab-surface-soft) !important;
        color: var(--idealab-text) !important;
    }
    .idealab-module .text-dark {
        color: var(--idealab-text) !important;
    }
    .idealab-module .text-muted { color: var(--idealab-muted) !important; }
    @media (max-width: 991.98px) {
        .idealab-layout { grid-template-columns: 1fr; }
        .idealab-side { display: block; }
        .idealab-side > .card { margin-bottom: .75rem; }
        .idealab-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .idealab-workflow-strip { grid-template-columns: 1fr; }
    }
</style>
@endpush
