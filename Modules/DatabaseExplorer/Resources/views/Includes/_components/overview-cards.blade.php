<div class="dbx-stats">
    <div class="dbx-stat">
        <span class="dbx-stat__label">Database</span>
        <strong class="dbx-stat__value">{{ $overview['databaseName'] ?? '—' }}</strong>
        <span class="dbx-stat__hint">{{ $overview['engine'] ?? '—' }}</span>
    </div>
    <div class="dbx-stat">
        <span class="dbx-stat__label">Size</span>
        <strong class="dbx-stat__value">{{ $formatBytes((int) ($overview['totalSizeBytes'] ?? 0)) }}</strong>
        <span class="dbx-stat__hint">Total database size</span>
    </div>
    <div class="dbx-stat">
        <span class="dbx-stat__label">Tables</span>
        <strong class="dbx-stat__value">{{ number_format((int) ($overview['tableCount'] ?? 0)) }}</strong>
        <span class="dbx-stat__hint">{{ number_format((int) ($overview['viewCount'] ?? 0)) }} views</span>
    </div>
    <div class="dbx-stat">
        <span class="dbx-stat__label">Indexes</span>
        <strong class="dbx-stat__value">{{ number_format((int) ($overview['indexCount'] ?? 0)) }}</strong>
        <span class="dbx-stat__hint">Across allowed schemas</span>
    </div>
    <div class="dbx-stat">
        <span class="dbx-stat__label">Estimated rows</span>
        <strong class="dbx-stat__value">{{ number_format((int) ($overview['estimatedRows'] ?? 0)) }}</strong>
        <span class="dbx-stat__hint">Planner estimate</span>
    </div>
    <div class="dbx-stat">
        <span class="dbx-stat__label">Health</span>
        <strong class="dbx-stat__value">{{ (int) ($overview['healthScore'] ?? 0) }}/100</strong>
        <span class="dbx-stat__hint">{{ ucfirst($overview['healthStatus'] ?? 'healthy') }}</span>
    </div>
</div>
