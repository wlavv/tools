@extends('permission-role-manager::layouts.module')
@section('module-content')
<div class="prm-card"><form><input class="prm-input" name="q" value="{{ request('q') }}" placeholder="Pesquisar acao ou email"></form></div>
<div class="prm-card"><table class="prm-table"><thead><tr><th>Data</th><th>Acao</th><th>User</th><th>Entidade</th><th>Detalhe</th></tr></thead><tbody>@foreach($logs as $log)<tr><td>{{ $log->created_at }}</td><td><strong>{{ $log->action }}</strong></td><td>{{ $log->user_email ?: '-' }}</td><td>{{ $log->entity_type }} #{{ $log->entity_id }}</td><td><details><summary>Ver JSON</summary><pre style="white-space:pre-wrap">{{ json_encode(['before'=>$log->before,'after'=>$log->after], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></details></td></tr>@endforeach</tbody></table>{{ $logs->links() }}</div>
@endsection
