@extends('layouts.app')

@section('content')
<div class="lsg-content">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">{{ __('module-health-bridge::messages.dashboard') }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('module-health-bridge.index') }}">{{ __('module-health-bridge::messages.title') }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ __('module-health-bridge::messages.result') }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">{{ __('module-health-bridge::messages.score') }}</div>
                    <div class="display-6">{{ $result['score'] }}%</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">{{ __('module-health-bridge::messages.status') }}</div>
                    <span class="badge bg-{{ $result['status'] === 'passed' ? 'success' : ($result['status'] === 'failed' ? 'danger' : 'warning') }}">
                        {{ $result['status'] }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">{{ __('module-health-bridge::messages.failed') }}</div>
                    <div class="h4 mb-0">{{ $result['failed_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-muted small">{{ __('module-health-bridge::messages.warnings') }}</div>
                    <div class="h4 mb-0">{{ $result['warning_count'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa-solid fa-list-check me-2"></i>{{ __('module-health-bridge::messages.findings') }}</h5>
            <a href="{{ route('module-health-bridge.index') }}" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-angle-left me-1"></i>{{ __('module-health-bridge::messages.back') }}
            </a>
        </div>
        <div class="card-body">
            @if(empty($result['findings']))
                <div class="alert alert-info mb-0">{{ __('module-health-bridge::messages.no_findings') }}</div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle datatable">
                        <thead>
                            <tr>
                                <th>{{ __('module-health-bridge::messages.code') }}</th>
                                <th>{{ __('module-health-bridge::messages.status') }}</th>
                                <th>{{ __('module-health-bridge::messages.severity') }}</th>
                                <th>{{ __('module-health-bridge::messages.message') }}</th>
                                <th>{{ __('module-health-bridge::messages.file') }}</th>
                                <th>{{ __('module-health-bridge::messages.recommendation') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($result['findings'] as $finding)
                                <tr>
                                    <td><code>{{ $finding['code'] }}</code></td>
                                    <td><span class="badge bg-secondary">{{ $finding['status'] }}</span></td>
                                    <td><span class="badge bg-light text-dark border">{{ $finding['severity'] }}</span></td>
                                    <td>
                                        <strong>{{ $finding['title'] }}</strong><br>
                                        <span class="text-muted small">{{ $finding['message'] }}</span>
                                    </td>
                                    <td><code class="small">{{ $finding['file_path'] }}</code></td>
                                    <td>{{ $finding['recommendation'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
