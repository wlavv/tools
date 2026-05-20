@extends('layouts.app')

@section('content')
<div class="idealab-module">
    @yield('idealab-content')
</div>
@endsection

@push('styles')
<style>
    .idealab-module { --idealab-border: rgba(15,23,42,.12); }
    .idealab-card { border: 1px solid var(--idealab-border); border-radius: 6px; box-shadow: none; }
    .idealab-soft { background: #f8fafc; }
    .idealab-score { font-weight: 700; font-size: 1.5rem; }
    .idealab-badge { border-radius: 4px; padding: .35rem .55rem; }
    .idealab-layout { display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 1rem; align-items: start; }
    .idealab-side { position: sticky; top: 1rem; }
    .idealab-side .list-group-item { border-color: var(--idealab-border); }
    .idealab-summary { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: .75rem; margin-bottom: 1rem; }
    .idealab-summary-item { border: 1px solid var(--idealab-border); border-radius: 6px; padding: .75rem; background: var(--bg-card-2, var(--card-bg, var(--bg-card, #2f3a48))); color: var(--text-primary, #f0f4f9); }
    .idealab-summary-item span { display: block; color: var(--text-muted, #9aa7b8); font-size: .75rem; text-transform: uppercase; }
    .idealab-summary-item strong { font-size: 1rem; }
    .idealab-section { scroll-margin-top: 1rem; }
    .idealab-chat-box { max-height: 560px; overflow-y: auto; background: #f8fafc; border: 1px solid var(--idealab-border); border-radius: 6px; padding: .75rem; }
    .idealab-chat-message { border-radius: 6px; padding: .75rem 1rem; margin-bottom: .75rem; background: #fff; border: 1px solid rgba(0,0,0,.06); }
    .idealab-chat-message.user { border-left: 4px solid #0d6efd; }
    .idealab-chat-message.assistant { border-left: 4px solid #198754; }
    .idealab-chat-message.provider { border-left: 4px solid #6f42c1; }
    .idealab-chat-message.system { border-left: 4px solid #64748b; }
    .idealab-chat-message.failed { border-left: 4px solid #dc3545; }
    .idealab-chat-message pre { white-space: pre-wrap; margin: .5rem 0 0; font-size: .84rem; color: #334155; }
    .idealab-chat-meta { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .4rem; color: #64748b; font-size: .78rem; }
    .idealab-chat-meta span { border: 1px solid rgba(15,23,42,.08); border-radius: 6px; padding: .15rem .4rem; background: #f8fafc; }
    .idealab-context-strip { display: flex; flex-wrap: wrap; gap: .5rem; }
    .idealab-context-strip span { display: inline-flex; align-items: center; gap: .45rem; padding: .45rem .65rem; border: 1px solid rgba(15,23,42,.08); border-radius: 6px; background: rgba(255,255,255,.72); color: #475569; font-size: .85rem; font-weight: 600; }
    .idealab-context-strip i { color: #0d6efd; }
    .idealab-workflow-strip { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: .5rem; }
    .idealab-workflow-step { border: 1px solid var(--idealab-border); border-radius: 6px; background: #fff; padding: .65rem; min-height: 92px; }
    .idealab-workflow-step.is-done { border-color: rgba(25,135,84,.35); background: #f3fbf7; }
    .idealab-workflow-step span { display: inline-flex; width: 24px; height: 24px; align-items: center; justify-content: center; border-radius: 50%; background: #e2e8f0; color: #334155; font-size: .8rem; font-weight: 700; }
    .idealab-workflow-step.is-done span { background: #198754; color: #fff; }
    .idealab-workflow-step strong { display: block; margin-top: .45rem; font-size: .85rem; }
    .idealab-workflow-step small { display: block; color: #64748b; margin-top: .25rem; }
    .idealab-issue-list { border: 1px solid var(--lsg-bo-btn-danger-border, rgba(248, 113, 113, .78)); border-radius: 6px; background: var(--bg-card-2, var(--card-bg, var(--bg-card, #2f3a48))); color: var(--text-primary, #f0f4f9); }
    .idealab-issue-row { padding: .65rem .75rem; border-bottom: 1px solid rgba(220,53,69,.12); }
    .idealab-issue-row:last-child { border-bottom: 0; }
    @media (max-width: 991.98px) {
        .idealab-layout { grid-template-columns: 1fr; }
        .idealab-side { display: block; }
        .idealab-side > .card { margin-bottom: .75rem; }
        .idealab-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .idealab-workflow-strip { grid-template-columns: 1fr; }
    }
</style>
@endpush
