@extends('job-queue-monitor::layouts.module')

@section('content')
@include('job-queue-monitor::partials.styles')
<div class="jqm-wrap">
    <div class="card jqm-card"><div class="card-body">
        <h5 class="mb-3">Definições ativas</h5>
        <pre class="jqm-pre">{{ json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        <div class="alert alert-info jqm-alert mt-3 mb-0">
            As definições são controladas por <code>.env</code> e <code>Config/config.php</code> para evitar alterações perigosas diretamente em produção.
        </div>
    </div></div>
</div>
@endsection
