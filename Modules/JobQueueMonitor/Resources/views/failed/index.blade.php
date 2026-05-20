@extends('job-queue-monitor::layouts.module')

@section('content')
@include('job-queue-monitor::partials.styles')
<div class="lsg-content jqm-wrap">
    <div class="card jqm-card"><div class="card-body">
        <h5 class="mb-3"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Jobs falhados por resolver</h5>
        <div class="table-responsive">
            <table class="table jqm-table table-hover mb-0">
                <thead><tr><th>Job</th><th>Queue</th><th>Erro</th><th>Tentativas</th><th>Falhou em</th><th></th></tr></thead>
                <tbody>
                @forelse($runs as $run)
                    <tr>
                        <td class="font-weight-bold">{{ class_basename($run->job_name) }}</td>
                        <td>{{ $run->queue ?: '-' }}</td>
                        <td class="text-danger">{{ \Illuminate\Support\Str::limit($run->exception_message, 120) }}</td>
                        <td>{{ $run->attempts }}</td>
                        <td>{{ optional($run->failed_at)->format('d/m/Y H:i:s') }}</td>
                        <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route('job_queue_monitor.show', $run) }}"><i class="fa-solid fa-eye"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Sem falhas abertas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $runs->links() }}</div>
    </div></div>
</div>
@endsection
