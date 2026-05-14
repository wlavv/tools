<h2>Job falhado no WebTools Manager</h2>
<p><strong>ID:</strong> #{{ $run->id }}</p>
<p><strong>Job:</strong> {{ $run->job_name }}</p>
<p><strong>Queue:</strong> {{ $run->queue ?: '-' }}</p>
<p><strong>Connection:</strong> {{ $run->connection ?: '-' }}</p>
<p><strong>Tentativas:</strong> {{ $run->attempts }}</p>
<p><strong>Falhou em:</strong> {{ optional($run->failed_at)->format('d/m/Y H:i:s') }}</p>
<p><strong>Erro:</strong> {{ $run->exception_message }}</p>
<p><strong>Ficheiro:</strong> {{ $run->exception_file }}:{{ $run->exception_line }}</p>
<hr>
<pre style="white-space:pre-wrap;background:#f8fafc;padding:12px;border:1px solid #e2e8f0;">{{ $run->exception_trace }}</pre>
