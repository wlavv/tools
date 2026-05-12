@extends('erp::layouts.module')

@section('erp-content')
@if(false)
<div class="erp-hero lsg-card">
    <div>
        <div class="erp-kicker">
            <i class="fa-solid fa-sliders"></i>
            ERP Configuration
        </div>
        <h1>Settings</h1>
        <p>Document types, statuses, workflows, numbering, widgets, timeline tasks and supplier terms.</p>
    </div>

    <a href="{{ route('erp.dashboard') }}" class="btn btn-outline-primary lsg-action-btn lsg-action-btn--primary">
        <i class="fa-solid fa-angle-left"></i> Back
    </a>
</div>
@endif

<div class="row g-3">
    <div class="col-lg-4">
        <div class="lsg-card erp-panel">
            <div class="erp-panel-title">
                <i class="fa-solid fa-file-lines"></i>
                Document Types
            </div>
            @foreach($documentTypes as $type)
                <div class="erp-config-row">
                    <span><i class="{{ $type->icon }}"></i> {{ $type->name }}</span>
                    <code>{{ $type->code }}</code>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-4">
        <div class="lsg-card erp-panel">
            <div class="erp-panel-title">
                <i class="fa-solid fa-tags"></i>
                Statuses
            </div>
            @foreach($statuses as $status)
                <div class="erp-config-row">
                    <span><i class="{{ $status->icon }} erp-status-icon" style="--erp-status-color: {{ $status->color }}"></i> {{ $status->name }}</span>
                    <code>{{ $status->scope }}.{{ $status->code }}</code>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-lg-4">
        <div class="lsg-card erp-panel">
            <div class="erp-panel-title">
                <i class="fa-solid fa-hashtag"></i>
                Numbering
            </div>
            @foreach($sequences as $sequence)
                <div class="erp-config-row">
                    <span>{{ $sequence->document_type_code }}</span>
                    <code>{{ $sequence->pattern }}</code>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="lsg-card erp-panel mt-3">
    <div class="erp-panel-title">
        <i class="fa-solid fa-timeline"></i>
        Timeline Tasks
    </div>

    <div class="erp-table-shell">
        <table class="table erp-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Step</th>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Required</th>
                </tr>
            </thead>
            <tbody>
                @foreach($timelineTasks as $task)
                    <tr>
                        <td><code>{{ $task->step_key }}</code></td>
                        <td><i class="{{ $task->icon }}"></i> {{ $task->title }}</td>
                        <td>{{ $task->status }}</td>
                        <td>{{ $task->is_required ? 'Yes' : 'No' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="lsg-card erp-panel mt-3">
    <div class="erp-panel-title">
        <i class="fa-solid fa-database"></i>
        Configurations
    </div>

    <div class="erp-table-shell">
        <table class="table erp-table align-middle mb-0">
            <thead>
                <tr>
                    <th>Group</th>
                    <th>Key</th>
                    <th>Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($configs as $config)
                    <tr>
                        <td>{{ $config->group }}</td>
                        <td><code>{{ $config->key }}</code></td>
                        <td>{{ $config->type }}</td>
                        <td>{{ $config->description }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
