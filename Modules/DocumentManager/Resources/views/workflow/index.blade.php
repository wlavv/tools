@extends('documentmanager::layouts.module')

@section('documentmanager-content')
    @php
        $workflowIcons = [
            'draft' => 'fa-regular fa-file',
            'pending_review' => 'fa-solid fa-magnifying-glass',
            'pending_approval' => 'fa-solid fa-clipboard-check',
            'approved' => 'fa-solid fa-circle-check',
            'rejected' => 'fa-solid fa-circle-xmark',
            'archived' => 'fa-solid fa-box-archive',
            'expired' => 'fa-solid fa-hourglass-end',
            'locked' => 'fa-solid fa-lock',
        ];

        $workflowTones = [
            'approved' => 'primary',
            'rejected' => 'danger',
            'expired' => 'danger',
            'locked' => 'warning',
            'pending_review' => 'warning',
            'pending_approval' => 'warning',
        ];
    @endphp

    <div class="dms-counter-line dms-counter-line--workflow">
        @foreach($stats as $state => $count)
            <div class="dms-panel dms-panel--{{ $workflowTones[$state] ?? 'primary' }}">
                <div class="dms-panel__icon"><i class="{{ $workflowIcons[$state] ?? 'fa-solid fa-circle-dot' }}"></i></div>
                <div>
                    <span>{{ str_replace('_', ' ', $state) }}</span>
                    <strong>{{ $count }}</strong>
                </div>
            </div>
        @endforeach
    </div>

    <div class="dms-grid dms-grid--2">
        <div class="dms-card">
            <h3>Approvals</h3>
            <table class="dms-table document-lsg-datatable">
                <thead><tr><th>Documento</th><th>Tipo</th><th>Estado</th><th>Due</th></tr></thead>
                <tbody>
                    @forelse($approvals as $approval)
                        <tr>
                            <td>#{{ $approval->document_id }}</td>
                            <td>{{ $approval->approval_type }}</td>
                            <td><span class="dms-badge">{{ $approval->status }}</span></td>
                            <td>{{ $approval->due_at ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Sem aprovacoes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="dms-card">
            <h3>Tasks</h3>
            <table class="dms-table document-lsg-datatable">
                <thead><tr><th>Tarefa</th><th>Prioridade</th><th>Estado</th><th>Due</th></tr></thead>
                <tbody>
                    @forelse($tasks as $task)
                        <tr>
                            <td>{{ $task->title }}</td>
                            <td>{{ $task->priority }}</td>
                            <td><span class="dms-badge">{{ $task->status }}</span></td>
                            <td>{{ $task->due_at ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Sem tarefas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
