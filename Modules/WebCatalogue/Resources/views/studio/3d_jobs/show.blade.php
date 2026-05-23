@extends('layouts.app')
@section('content')
@include('webcatalogue::Includes.css')
<div class="webcatalogue-shell wc-studio-shell" data-3d-job-status-url="{{ route('webcatalogue.studio.3d_jobs.status', $item) }}">
@if(session('success'))<div class="wc-alert">{{ session('success') }}</div>@endif

<div class="wc-studio-hero wc-studio-hero-detail">
    <div>
        <span class="wc-eyebrow"><i class="fa-solid fa-cube"></i> 3D Studio Job</span>
        <h2>{{ $item->product?->name ? strip_tags($item->product?->name) : '3D Job #' . $item->id }}</h2>
        <p>{{ $item->prompt ?: '3D, AR and VR generation/asset job.' }}</p>
        <div class="wc-studio-steps">
            <span><i class="fa-solid fa-store"></i>{{ $item->store?->name ?? 'â€”' }}</span>
            <span><i class="fa-solid fa-box-open"></i>{{ $item->product?->reference ?? 'â€”' }}</span>
            <span><i class="fa-solid fa-plug"></i>{{ $item->provider }}</span>
            <span><i class="fa-solid fa-signal"></i>{{ $item->provider_status ?: 'â€”' }} Â· {{ (int)($item->progress ?? 0) }}%</span>
            <span><i class="fa-solid fa-layer-group"></i>{{ $item->input_mode }}</span>
        </div>
    </div>
    <div class="wc-studio-status-orb wc-status-{{ $item->status }}" id="wc3dStatusOrb"><i class="fa-solid fa-cube"></i><span id="wc3dStatusText">{{ $item->status }}</span></div>
</div>

<div class="wc-editor-layout">
    <div>
        <div class="wc-studio-timeline">
            <div class="wc-studio-timeline-step is-done"><i class="fa-solid fa-images"></i><strong>Input</strong><span>{{ count((array) $item->source_resource_ids) }} source image(s)</span></div>
            <div id="wc3dGenerationStep" class="wc-studio-timeline-step {{ in_array($item->status, ['processing','completed']) ? 'is-done' : '' }}"><i class="fa-solid fa-gears"></i><strong>Generation</strong><span id="wc3dProviderProgress">{{ ucfirst($item->provider ?? 'manual') }} Â· {{ (int)($item->progress ?? 0) }}%</span></div>
            <div id="wc3dModelStep" class="wc-studio-timeline-step {{ $item->resultResource ? 'is-done' : '' }}"><i class="fa-solid fa-cube"></i><strong>3D model</strong><span id="wc3dModelLabel">{{ $item->resultResource->filename ?? 'Pending' }}</span></div>
            <div id="wc3dImmersiveStep" class="wc-studio-timeline-step {{ ($item->arResource || $item->vrResource) ? 'is-done' : '' }}"><i class="fa-solid fa-vr-cardboard"></i><strong>Immersive</strong><span id="wc3dImmersiveLabel">AR/VR exports</span></div>
        </div>

        <div class="wc-card wc-spaced-card">
            <div class="wc-section-head"><div><span class="wc-eyebrow"><i class="fa-solid fa-diagram-project"></i> Outputs</span><h3>Linked resources</h3></div></div>
            <div class="wc-output-grid">
                <div class="wc-output-card"><i class="fa-solid fa-cube"></i><strong>3D model</strong><span id="wc3dOutputModel">{{ $item->resultResource->filename ?? 'Not uploaded yet' }}</span></div>
                <div class="wc-output-card"><i class="fa-solid fa-mobile-screen"></i><strong>AR export</strong><span id="wc3dOutputAr">{{ $item->arResource->filename ?? 'Not uploaded yet' }}</span></div>
                <div class="wc-output-card"><i class="fa-solid fa-vr-cardboard"></i><strong>VR export</strong><span id="wc3dOutputVr">{{ $item->vrResource->filename ?? 'Not uploaded yet' }}</span></div>
            </div>
        </div>

        @if($item->error_message)
            <div class="wc-card wc-spaced-card wc-error-card"><i class="fa-solid fa-triangle-exclamation"></i><div><strong>Generation error</strong><p>{{ $item->error_message }}</p></div></div>
        @endif
    </div>
    <aside class="wc-preview-panel">
        <div class="wc-preview-card wc-studio-preview-card">
            <div class="wc-preview-media"><i class="fa-solid fa-cube wc-preview-icon"></i></div>
            <div class="wc-preview-body"><h4>Preview status</h4><p class="wc-muted" id="wc3dPreviewStatus">{{ $item->resultResource ? '3D model resource is available for viewer integration.' : 'The job is queued/processing automatically. This panel refreshes the status every few seconds.' }}</p></div>
        </div>
        <div class="wc-preview-card"><div class="wc-preview-body"><h4>Provider</h4><p class="wc-muted"><strong>{{ $item->provider }}</strong><br>Task: {{ $item->provider_task_id ?: 'â€”' }}<br>Status: <span id="wc3dProviderStatus">{{ $item->provider_status ?: 'â€”' }}</span><br>Progress: <span id="wc3dProgress">{{ (int)($item->progress ?? 0) }}%</span></p></div></div>
        <div class="wc-preview-card"><div class="wc-preview-body"><h4>Job controls</h4><div class="wc-actions-row">
            @if(!in_array($item->status, ['queued','processing','completed']))
                <form method="POST" action="{{ route('webcatalogue.studio.3d_jobs.run', $item) }}">@csrf<button class="wc-action-link btn-outline-primary" type="submit"><i class="fa-solid fa-play"></i> Run now</button></form>
            @endif
            @if($item->result_resource_id && $item->product)
                <a class="wc-action-link btn-outline-primary" href="{{ route('webcatalogue.products.viewer', $item->product) }}"><i class="fa-solid fa-cube"></i> View 3D</a>
            @endif
        </div></div></div>
    </aside>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const shell = document.querySelector('[data-3d-job-status-url]');
    if (!shell) return;

    const url = shell.getAttribute('data-3d-job-status-url');
    const terminal = ['completed', 'failed'];
    const statusText = document.getElementById('wc3dStatusText');
    const statusOrb = document.getElementById('wc3dStatusOrb');
    const generationStep = document.getElementById('wc3dGenerationStep');
    const modelStep = document.getElementById('wc3dModelStep');
    const immersiveStep = document.getElementById('wc3dImmersiveStep');
    const modelLabel = document.getElementById('wc3dModelLabel');
    const immersiveLabel = document.getElementById('wc3dImmersiveLabel');
    const outputModel = document.getElementById('wc3dOutputModel');
    const outputAr = document.getElementById('wc3dOutputAr');
    const outputVr = document.getElementById('wc3dOutputVr');
    const previewStatus = document.getElementById('wc3dPreviewStatus');
    const providerStatus = document.getElementById('wc3dProviderStatus');
    const providerProgress = document.getElementById('wc3dProviderProgress');
    const progress = document.getElementById('wc3dProgress');

    let currentStatus = statusText ? statusText.textContent.trim() : null;

    function setStatus(data) {
        if (!data || !data.status) return;
        currentStatus = data.status;
        if (statusText) statusText.textContent = data.status;
        if (statusOrb) statusOrb.className = 'wc-studio-status-orb wc-status-' + data.status;
        if (providerStatus) providerStatus.textContent = data.provider_status || 'â€”';
        if (progress) progress.textContent = (data.progress || 0) + '%';
        if (providerProgress) providerProgress.textContent = (data.provider || 'Provider') + ' Â· ' + (data.progress || 0) + '%';

        if (['processing', 'completed'].includes(data.status) && generationStep) generationStep.classList.add('is-done');
        if (data.result_resource_id && modelStep) modelStep.classList.add('is-done');
        if ((data.ar_resource_id || data.vr_resource_id) && immersiveStep) immersiveStep.classList.add('is-done');

        if (data.result_resource_id && modelLabel) modelLabel.textContent = 'Generated model available';
        if ((data.ar_resource_id || data.vr_resource_id) && immersiveLabel) immersiveLabel.textContent = 'AR/VR resources available';
        if (data.result_resource_id && outputModel) outputModel.textContent = 'Generated model available';
        if (data.ar_resource_id && outputAr) outputAr.textContent = 'AR export available';
        if (data.vr_resource_id && outputVr) outputVr.textContent = 'VR scene available';
        if (previewStatus && data.status === 'completed') previewStatus.textContent = '3D, AR and VR resources are now available. A notification was sent to your notification center.';
        if (previewStatus && data.status === 'failed') previewStatus.textContent = data.error_message || 'The generation failed.';

        if (terminal.includes(data.status)) {
            window.clearInterval(timer);
        }
    }

    async function poll() {
        try {
            const response = await fetch(url, {headers: {'Accept': 'application/json'}});
            if (response.ok) setStatus(await response.json());
        } catch (error) {}
    }

    const timer = window.setInterval(poll, 4000);
    if (!terminal.includes(currentStatus)) poll();
});
</script>
@endsection


