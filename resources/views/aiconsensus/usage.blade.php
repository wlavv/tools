@extends('layouts.app')

@section('content')
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>AI Usage (last {{ $days }} days)</h2>
    <div>
      <a class="btn btn-outline-secondary btn-sm" href="{{ route('aiconsensus.usage', ['days'=>7]) }}">7d</a>
      <a class="btn btn-outline-secondary btn-sm" href="{{ route('aiconsensus.usage', ['days'=>30]) }}">30d</a>
      <a class="btn btn-outline-secondary btn-sm" href="{{ route('aiconsensus.usage', ['days'=>90]) }}">90d</a>
      <a class="btn btn-outline-primary btn-sm" href="{{ route('aiconsensus.index') }}">Runs</a>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Runs summary</div>
    <div class="card-body">
      <div>Total runs: <strong>{{ $runs->total_runs ?? 0 }}</strong></div>
      <div>Done: <strong>{{ $runs->done_runs ?? 0 }}</strong></div>
      <div>Failed: <strong>{{ $runs->failed_runs ?? 0 }}</strong></div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Provider status</div>
    <div class="card-body">
      <table class="table table-sm">
        <thead><tr><th>Provider</th><th>Key</th><th>Model</th><th>Last error</th></tr></thead>
        <tbody>
          @foreach($providerStatus as $p => $s)
            <tr>
              <td><strong>{{ $p }}</strong></td>
              <td>{!! $s['key_set'] ? '<span class="badge bg-success">set</span>' : '<span class="badge bg-danger">missing</span>' !!}</td>
              <td><code>{{ $s['model'] }}</code></td>
              <td>
                @if(isset($lastErrors[$p]) && $lastErrors[$p]->error)
                  <div class="text-danger" style="max-width:900px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    {{ $lastErrors[$p]->error }}
                  </div>
                  <small class="text-muted">{{ $lastErrors[$p]->updated_at }}</small>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      <div class="text-muted">Note: providers don’t expose a universal “tokens remaining”; this is based on your actual usage captured by this system.</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Usage by provider</div>
    <div class="card-body">
      <table class="table table-striped">
        <thead><tr><th>Provider</th><th>Requests</th><th>Tokens in</th><th>Tokens out</th><th>Avg latency (ms)</th><th>Failed</th></tr></thead>
        <tbody>
          @foreach($byProvider as $r)
            <tr>
              <td><strong>{{ $r->provider }}</strong></td>
              <td>{{ $r->requests }}</td>
              <td>{{ $r->tokens_in }}</td>
              <td>{{ $r->tokens_out }}</td>
              <td>{{ (int) $r->avg_latency_ms }}</td>
              <td>{{ $r->failed_count }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

</div>
@endsection