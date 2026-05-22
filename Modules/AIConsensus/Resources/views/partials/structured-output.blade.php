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
    $summaryKeys = ['summary', 'executive_summary', 'overview', 'conclusion', 'final_answer'];
    $primarySummaryKey = is_array($payload) ? collect($summaryKeys)->first(fn ($key) => isset($payload[$key]) && !is_array($payload[$key])) : null;
    $primarySummary = $primarySummaryKey ? $payload[$primarySummaryKey] : null;
    $sectionOrder = ['recommendations', 'next_actions', 'actions', 'priorities', 'risks', 'assumptions', 'findings', 'validation', 'implementation_plan'];
    $orderedPayload = [];
    if (is_array($payload)) {
        foreach ($sectionOrder as $key) {
            if (array_key_exists($key, $payload) && $key !== $primarySummaryKey) {
                $orderedPayload[$key] = $payload[$key];
            }
        }

        foreach ($payload as $key => $value) {
            if ($key !== $primarySummaryKey && !array_key_exists($key, $orderedPayload)) {
                $orderedPayload[$key] = $value;
            }
        }
    }
@endphp

<div class="ai-structured-output {{ $compact ? 'ai-structured-output--compact' : '' }}">
    <style>
        .ai-structured-output{display:grid;gap:1rem}
        .ai-structured-toolbar{display:flex;align-items:center;justify-content:flex-end;gap:.75rem;flex-wrap:wrap}
        .ai-output-type{display:inline-flex;align-items:center;gap:.4rem;border:1px solid rgba(37,99,235,.16);background:rgba(37,99,235,.08);color:#2563eb;border-radius:5px;padding:.35rem .55rem;font-size:.75rem;font-weight:800}
        .ai-output-summary{border:1px solid rgba(37,99,235,.14);border-left:4px solid #2563eb;border-radius:6px;background:linear-gradient(180deg,rgba(37,99,235,.06),rgba(37,99,235,.025));padding:1rem 1.1rem}
        .ai-output-summary span{display:block;margin-bottom:.35rem;font-size:.72rem;text-transform:uppercase;letter-spacing:.08em;color:#2563eb;font-weight:900}
        .ai-output-summary p{margin:0;color:#111827;font-size:.98rem;line-height:1.65;white-space:pre-wrap}
        .ai-structured-section{border:1px solid rgba(15,23,42,.1);border-radius:6px;background:#fff;padding:1rem;display:grid;gap:.75rem}
        .ai-structured-section--nested{background:#f8fafc}
        .ai-structured-section h6{margin:0;font-size:.82rem;font-weight:900;color:#111827;text-transform:uppercase;letter-spacing:.06em}
        .ai-structured-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:.75rem}
        .ai-structured-field{border:1px solid rgba(15,23,42,.08);border-radius:6px;background:#f8fafc;padding:.75rem;display:grid;gap:.3rem}
        .ai-structured-field span,.ai-structured-text span{font-size:.72rem;text-transform:uppercase;color:#64748b;font-weight:850;letter-spacing:.04em}
        .ai-structured-field strong{font-size:.93rem;font-weight:600;color:#111827;white-space:normal;line-height:1.55}
        .ai-structured-block{min-width:0}
        .ai-structured-list{margin:0;padding:0;display:grid;gap:.5rem;list-style:none}
        .ai-structured-list li{color:#111827;border:1px solid rgba(15,23,42,.08);background:#f8fafc;border-radius:6px;padding:.7rem .8rem;line-height:1.55}
        .ai-structured-list-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:.75rem}
        .ai-structured-list-card{border:1px solid rgba(15,23,42,.08);border-radius:6px;background:#f8fafc;padding:.75rem}
        .ai-structured-text p{margin:.2rem 0 0;white-space:pre-wrap;line-height:1.6;color:#111827}
        .ai-structured-raw{border-top:1px solid rgba(15,23,42,.08);padding-top:.75rem}
        .ai-structured-raw summary{cursor:pointer;color:#2563eb;font-weight:800}
        .ai-structured-raw pre{white-space:pre-wrap;max-height:360px;overflow:auto;margin:.5rem 0 0;background:#0f172a;color:#e2e8f0;border-radius:6px;padding:.85rem}
        .ai-structured-output--compact .ai-structured-section{padding:.65rem}
        .ai-structured-output--compact .ai-structured-grid{grid-template-columns:1fr}
        .ai-structured-output--compact .ai-structured-list-cards{grid-template-columns:1fr}
    </style>

    @if(is_array($payload))
        <div class="ai-structured-toolbar">
            <span class="ai-output-type"><i class="fa-solid fa-code-branch"></i> structured output</span>
        </div>
    @endif

    @if(is_array($payload))
        @if($primarySummary)
            <section class="ai-output-summary">
                <span>{{ str($primarySummaryKey)->replace(['_', '-'], ' ')->headline() }}</span>
                <p>{{ $primarySummary }}</p>
            </section>
        @endif

        @if(!empty($orderedPayload))
            @foreach($orderedPayload as $key => $value)
                @include('ai-consensus::partials.structured-node', ['nodeKey' => $key, 'value' => $value, 'depth' => 0])
            @endforeach
        @elseif(!$primarySummary)
            @include('ai-consensus::partials.structured-node', ['value' => $payload, 'depth' => 0])
        @endif

        <details class="ai-structured-raw">
            <summary>JSON bruto</summary>
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
