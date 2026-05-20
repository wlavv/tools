@php
    $rawContent = (string) ($content ?? '');
    $payload = $payload ?? null;
    $title = $title ?? null;
    $compact = (bool) ($compact ?? false);

    if (!is_array($payload)) {
        $decoded = json_decode($rawContent, true);

        if (!is_array($decoded) && preg_match('/```(?:json)?\s*(\{.*\}|\[.*\])\s*```/is', $rawContent, $match)) {
            $decoded = json_decode($match[1], true);
        }

        if (!is_array($decoded)) {
            $startObject = strpos($rawContent, '{');
            $endObject = strrpos($rawContent, '}');
            if ($startObject !== false && $endObject !== false && $endObject > $startObject) {
                $decoded = json_decode(substr($rawContent, $startObject, $endObject - $startObject + 1), true);
            }
        }

        $payload = is_array($decoded) ? $decoded : null;
    }

    $prettyJson = is_array($payload)
        ? json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)
        : $rawContent;
@endphp

<div class="ai-structured-output {{ $compact ? 'ai-structured-output--compact' : '' }}">
    <style>
        .ai-structured-output{display:grid;gap:.75rem}
        .ai-structured-toolbar{display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap}
        .ai-structured-toolbar strong{font-size:.95rem}
        .ai-structured-section{border:1px solid rgba(15,23,42,.1);border-radius:6px;background:#fff;padding:.85rem;display:grid;gap:.65rem}
        .ai-structured-section--nested{background:#f8fafc}
        .ai-structured-section h6{margin:0;font-size:.86rem;font-weight:800;color:#111827}
        .ai-structured-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.65rem}
        .ai-structured-field{border:1px solid rgba(15,23,42,.08);border-radius:6px;background:#fff;padding:.65rem;display:grid;gap:.25rem}
        .ai-structured-field span,.ai-structured-text span{font-size:.72rem;text-transform:uppercase;color:#64748b;font-weight:800}
        .ai-structured-field strong{font-size:.9rem;font-weight:600;color:#111827;white-space:normal}
        .ai-structured-block{min-width:0}
        .ai-structured-list{margin:0;padding-left:1.1rem;display:grid;gap:.35rem}
        .ai-structured-list li{color:#111827}
        .ai-structured-list-cards{display:grid;gap:.65rem}
        .ai-structured-list-card{border:1px solid rgba(15,23,42,.08);border-radius:6px;background:#fff;padding:.65rem}
        .ai-structured-text p{margin:.2rem 0 0;white-space:pre-wrap}
        .ai-structured-raw summary{cursor:pointer;color:#0d6efd;font-weight:700}
        .ai-structured-raw pre{white-space:pre-wrap;max-height:360px;overflow:auto;margin:.5rem 0 0;background:#0f172a;color:#e2e8f0;border-radius:6px;padding:.75rem}
        .ai-structured-output--compact .ai-structured-section{padding:.65rem}
        .ai-structured-output--compact .ai-structured-grid{grid-template-columns:1fr}
    </style>

    @if($title || is_array($payload))
        <div class="ai-structured-toolbar">
            @if($title)
                <strong>{{ $title }}</strong>
            @endif
            @if(is_array($payload))
                <span class="badge bg-light text-dark border">structured JSON</span>
            @endif
        </div>
    @endif

    @if(is_array($payload))
        @include('ai-consensus::partials.structured-node', ['value' => $payload, 'depth' => 0])
        <details class="ai-structured-raw">
            <summary>Ver JSON bruto</summary>
            <pre>{{ $prettyJson }}</pre>
        </details>
    @else
        <div class="ai-structured-section">
            <div class="ai-structured-text">
                <p>{{ $rawContent ?: 'Sem output ainda.' }}</p>
            </div>
        </div>
    @endif
</div>
