<div class="ai-grid">
    <div class="ai-card"><div class="ai-muted">Total runs</div><div class="ai-stat-value">{{ $stats['total_runs'] }}</div></div>
    <div class="ai-card"><div class="ai-muted">Queued</div><div class="ai-stat-value">{{ $stats['queued_runs'] }}</div></div>
    <div class="ai-card"><div class="ai-muted">Running</div><div class="ai-stat-value">{{ $stats['running_runs'] }}</div></div>
    <div class="ai-card"><div class="ai-muted">Done</div><div class="ai-stat-value">{{ $stats['done_runs'] }}</div></div>
    <div class="ai-card"><div class="ai-muted">Failed</div><div class="ai-stat-value">{{ $stats['failed_runs'] }}</div></div>
    <div class="ai-card"><div class="ai-muted">Custo total estimado</div><div class="ai-stat-value">${{ number_format($stats['total_cost_usd'], 4) }}</div></div>
    <div class="ai-card">
        <div class="ai-muted" style="padding: 10px;">
            <a href="{{ route('ai_consensus.create') }}" class="btn btn-sm btn-outline-success"> <i class="fa-solid fa-plus" style="font-size: 20px; height: 25px;padding: 5px;"></i> </a>
            <button onclick="$('#showCards').toggle()" class="btn btn-sm btn-outline-primary" style="float: right;"><i class="fa-solid fa-cog" style="font-size: 20px; height: 25px;padding: 5px;"></i></button>
        </div>
    </div>
</div>
