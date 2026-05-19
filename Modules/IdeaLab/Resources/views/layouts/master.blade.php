@extends('layouts.app')

@section('content')
<div class="idealab-module">
    @yield('idealab-content')
</div>
@endsection

@push('styles')
<style>
    .idealab-card { border: 1px solid rgba(25,135,84,.16); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,.04); }
    .idealab-soft { background: linear-gradient(135deg, rgba(25,135,84,.07), rgba(13,110,253,.04)); }
    .idealab-score { font-weight: 700; font-size: 1.5rem; }
    .idealab-badge { border-radius: 999px; padding: .35rem .65rem; }
    .idealab-chat-box { min-height: 280px; max-height: 520px; overflow-y: auto; background: #f8faf9; border-radius: 14px; padding: 1rem; }
    .idealab-chat-message { border-radius: 14px; padding: .75rem 1rem; margin-bottom: .75rem; background: #fff; border: 1px solid rgba(0,0,0,.05); }
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
</style>
@endpush
