<article class="ci-item ci-item--{{ $item['severity'] ?? 'info' }}">
    <div class="ci-item-top">
        <div>
            <div class="ci-item-title">{{ $item['title'] ?? 'Inspection item' }}</div>
            <div class="ci-item-message">{{ $item['message'] ?? '' }}</div>
        </div>
        <span class="ci-badge ci-badge--{{ $item['severity'] ?? 'info' }}">{{ $severityLabels[$item['severity'] ?? 'info'] ?? 'Info' }}</span>
    </div>

    @if(!empty($item['suggestion']))
        <div class="ci-suggestion"><i class="fa-solid fa-lightbulb me-1"></i>{{ $item['suggestion'] }}</div>
    @endif

    @if(!empty($item['meta']))
        <details class="ci-meta">
            <summary>Detalhes tecnicos</summary>
            <pre class="mb-0 mt-2">{{ json_encode($item['meta'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </details>
    @endif
</article>
