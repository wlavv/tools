@extends('layouts.app')

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h2>Run #{{ $run->id }} <small class="text-muted">{{ $run->status }}</small></h2>
      <div class="text-muted">{{ $run->template_key }} · {{ $run->created_at }}</div>
    </div>
    <div>
      <a class="btn btn-outline-secondary" href="{{ route('aiconsensus.index') }}">Back</a>
    </div>
  </div>
  </div>
</div>
</div>
    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
  <div class="card mb-3">
    <div class="card-header">Prompt</div>
    <div class="card-body"><pre style="white-space:pre-wrap">{{ $run->prompt }}</pre></div>
  </div>
  </div>
  </div>
  </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">
  <div class="card mb-3">
    <div class="card-header">Final Answer</div>
    <div class="card-body">
      @if($run->final_answer)
        <pre style="white-space:pre-wrap">{{ $run->final_answer }}</pre>
      @else
        <div class="text-muted">Aguardando conclusão… (refresh)</div>
      @endif
    </div>
  </div>
</div>
</div>
</div>
    <div class="row">
        <div class="col-lg-12">
            <div class="navbar navbar-light customPanel">

  <div class="card">
    <div class="card-header">Provider drafts</div>
    <div class="card-body">
      @foreach($run->responses as $resp)
        <details class="mb-3">
          <summary><strong>{{ $resp->provider }}</strong> — {{ $resp->status }} @if($resp->model) ({{ $resp->model }}) @endif</summary>
          @if($resp->error)
            <div class="text-danger mt-2">{{ $resp->error }}</div>
          @endif
          @if($resp->raw_output)
            <pre class="mt-2" style="white-space:pre-wrap">{{ $resp->raw_output }}</pre>
          @endif
        </details>
      @endforeach
    </div>
  </div>
</div>
  </div>
</div>
@endsection