@extends('erp::layouts.module')

@section('erp-content')
@if(false)
<div class="erp-hero lsg-card">
    <div>
        <div class="erp-kicker">
            <i class="fa-solid fa-route"></i>
            ERP Sequential Flow
        </div>
        <h1>ERP Timeline</h1>
        <p>Fluxo guiado desde a intenção de compra até à receção, validação e fecho operacional.</p>
    </div>

    <div class="erp-hero-actions">
        <a href="{{ route('erp.settings.index') }}" class="btn btn-outline-primary lsg-action-btn lsg-action-btn--primary">
            <i class="fa-solid fa-cog"></i> Settings
        </a>
        <a href="#" class="btn btn-outline-success lsg-action-btn lsg-action-btn--success">
            <i class="fa-solid fa-plus"></i> Nova Order Note
        </a>
    </div>
</div>
@endif

<div class="erp-timeline-card lsg-card">
    <div class="erp-panel-header">
        <div>
            <div class="erp-panel-title mb-0">
                <i class="fa-solid fa-timeline"></i>
                Caminho operacional
            </div>
            <small>Timeline sequencial: o utilizador sabe onde está, o que falta e qual é o próximo passo.</small>
        </div>

        <span class="erp-badge erp-badge-info">
            {{ $steps->sum('pending') }} tarefas pendentes
        </span>
    </div>

    <div class="erp-flow-tabs" role="tablist">
        @foreach($steps as $index => $step)
            <a
                class="erp-flow-tab erp-flow-tab--{{ $step['status'] }} {{ $step['status'] === 'active' ? 'is-active' : '' }}"
                href="{{ $step['status'] === 'locked' ? '#' : route('erp.timeline', ['step' => $step['key']]) }}"
                aria-disabled="{{ $step['status'] === 'locked' ? 'true' : 'false' }}"
            >
                <span class="erp-flow-tab-index">{{ $index + 1 }}</span>
                <span class="erp-flow-tab-icon"><i class="{{ $step['icon'] }}"></i></span>
                <span class="erp-flow-tab-text">
                    <strong>{{ $step['label'] }}</strong>
                    <small>{{ $step['pending'] }} pendentes</small>
                </span>
            </a>
        @endforeach
    </div>
</div>

<div class="row g-3 mt-1">
    <div class="col-xl-8 col-lg-8">
        <div class="lsg-card erp-panel erp-stage-panel">
            <div class="erp-panel-header">
                <div>
                    <div class="erp-panel-title mb-0">
                        <i class="{{ $activeStep['icon'] }}"></i>
                        {{ $activeStep['label'] }}
                    </div>
                    <small>{{ $activeStep['description'] }}</small>
                </div>

                <span class="erp-stage-status erp-stage-status--{{ $activeStep['status'] }}">
                    {{ ucfirst($activeStep['status']) }}
                </span>
            </div>

            <div class="erp-current-step">
                <div class="erp-current-step-main">
                    <div class="erp-current-step-icon">
                        <i class="{{ $activeStep['icon'] }}"></i>
                    </div>

                    <div>
                        <h2>{{ $activeStep['label'] }}</h2>
                        <p>{{ $activeStep['description'] }}</p>
                    </div>
                </div>

                <div class="erp-current-step-progress">
                    <div class="erp-progress-label">
                        <span>Progresso da etapa</span>
                        <strong>{{ $activeStep['progress'] }}%</strong>
                    </div>
                    <div class="erp-progress-track">
                        <span style="--erp-progress: {{ $activeStep['progress'] }}%"></span>
                    </div>
                </div>
            </div>

            <div class="erp-next-action-box">
                <div>
                    <strong>Próxima ação recomendada</strong>
                    <p>Selecionar fornecedor e carregar contexto operacional: condições, moeda, produtos, order notes abertas e documentos pendentes.</p>
                </div>

                <a href="#" class="btn btn-outline-primary lsg-action-btn lsg-action-btn--primary">
                    <i class="fa-solid fa-arrow-right"></i>
                    Continuar
                </a>
            </div>

            <div class="erp-table-shell mt-3">
                <table class="table erp-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Etapa</th>
                            <th>Tarefa</th>
                            <th>Estado</th>
                            <th class="text-end">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeStep['tasks'] as $task)
                            <tr>
                                <td>{{ $activeStep['label'] }}</td>
                                <td>
                                    <span class="erp-task-title">
                                        @if(!empty($task['icon']))
                                            <i class="{{ $task['icon'] }}"></i>
                                        @endif
                                        {{ $task['title'] }}
                                    </span>
                                </td>
                                <td>
                                    <span class="erp-badge {{ ($task['status'] ?? 'pending') === 'completed' ? 'erp-badge-success' : 'erp-badge-warning' }}">
                                        {{ ucfirst($task['status'] ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-sm btn-outline-primary lsg-action-btn lsg-action-btn--compact">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-4">
        <div class="lsg-card erp-panel">
            <div class="erp-panel-title">
                <i class="fa-solid fa-list-check"></i>
                Tarefas Pendentes
            </div>

            <div class="erp-task-stack">
                @foreach($steps as $step)
                    <div class="erp-task-group {{ $step['status'] === 'active' ? 'is-active' : '' }}">
                        <div class="erp-task-group-header">
                            <span>
                                <i class="{{ $step['icon'] }}"></i>
                                {{ $step['label'] }}
                            </span>
                            <strong>{{ $step['pending'] }}</strong>
                        </div>

                        <div class="erp-task-list">
                            @foreach($step['tasks'] as $task)
                                <label class="erp-task-item">
                                    <input type="checkbox" {{ $step['status'] === 'locked' ? 'disabled' : '' }} {{ ($task['status'] ?? 'pending') === 'completed' ? 'checked' : '' }}>
                                    <span>{{ $task['title'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="lsg-card erp-panel mt-3">
            <div class="erp-panel-title">
                <i class="fa-solid fa-compass"></i>
                Contexto da Etapa
            </div>

            <div class="erp-stage-summary">
                <div>
                    <span>Etapa atual</span>
                    <strong>{{ $activeStep['label'] }}</strong>
                </div>
                <div>
                    <span>Pendentes</span>
                    <strong>{{ $activeStep['pending'] }}</strong>
                </div>
                <div>
                    <span>Bloqueios</span>
                    <strong>0</strong>
                </div>
            </div>

            <div class="erp-suggestion mt-3">
                <strong>Prioridade</strong>
                <span>Começar sempre pelo fornecedor. Todas as etapas seguintes dependem desse contexto.</span>
            </div>

            <div class="erp-suggestion">
                <strong>Regra</strong>
                <span>Uma etapa bloqueada só abre quando a anterior tem os mínimos operacionais concluídos.</span>
            </div>
        </div>
    </div>
</div>
@endsection
