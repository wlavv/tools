<div class="ih-side">
    <a href="{{ route('integration_health.index') }}" class="{{ request()->routeIs('integration_health.index') ? 'active' : '' }}"><i class="fa-solid fa-gauge-high"></i> Overview</a>
    <a href="{{ route('integration_health.integrations.index') }}" class="{{ request()->routeIs('integration_health.integrations.*') ? 'active' : '' }}"><i class="fa-solid fa-plug-circle-bolt"></i> Integrations</a>
    <a href="{{ route('integration_health.events.index', ['status' => 'open']) }}" class="{{ request()->routeIs('integration_health.events.*') ? 'active' : '' }}"><i class="fa-solid fa-triangle-exclamation"></i> Events</a>
    <hr>
    <div class="ih-muted">Operational tabs prepared for future inspectors:</div>
    <a href="#"><i class="fa-solid fa-list-check"></i> Queues</a>
    <a href="#"><i class="fa-solid fa-clock-rotate-left"></i> Cron Jobs</a>
    <a href="#"><i class="fa-solid fa-code-branch"></i> Webhooks</a>
    <a href="#"><i class="fa-solid fa-rotate"></i> Sync Health</a>
</div>
