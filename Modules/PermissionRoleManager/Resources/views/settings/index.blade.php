@extends('permission-role-manager::layouts.module')
@section('module-content')
<div class="prm-card"><pre style="white-space:pre-wrap">{{ json_encode($config, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></div>
@endsection
