<?php

namespace Modules\DataExportCenter\Services;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Modules\DataExportCenter\Models\DataExportReportTemplate;
use RuntimeException;

class ReportRendererService
{
    public function renderHtml(array $payload, ?DataExportReportTemplate $template = null): string
    {
        $templatePayload = $this->payload($payload, $template);

        $header = $this->renderFragment($template?->header_html ?: $this->defaultHeader(), $templatePayload);
        $footer = $this->renderFragment($template?->footer_html ?: $this->defaultFooter(), $templatePayload);
        $body = $this->renderFragment($template?->body_html ?: $this->defaultBody(), $templatePayload);
        $css = $template?->css ?: $this->defaultCss();
        $title = $this->renderFragment($template?->title_template ?: '{{ $title }}', $templatePayload);

        return view('data-export-center::reports.report-html', [
            'title' => $title,
            'header' => $header,
            'footer' => $footer,
            'body' => $body,
            'css' => $css,
        ])->render();
    }

    public function storeHtml(string $html, string $disk, string $path): void
    {
        Storage::disk($disk)->put($path, $html);
    }

    public function storePdf(string $html, string $disk, string $path): void
    {
        $facade = config('data-export-center.reports.pdf.dompdf_facade');

        if (! config('data-export-center.reports.pdf.enabled', true) || ! class_exists($facade)) {
            throw new RuntimeException('PDF export requires barryvdh/laravel-dompdf or a compatible PDF renderer.');
        }

        $pdf = $facade::loadHTML($html);
        Storage::disk($disk)->put($path, $pdf->output());
    }

    private function renderFragment(string $template, array $payload): string
    {
        return Blade::render($template, $payload, deleteCachedView: true);
    }

    private function payload(array $payload, ?DataExportReportTemplate $template): array
    {
        $payload['template'] = $template;
        $payload['generated_at'] = now();

        return $payload;
    }

    private function defaultHeader(): string
    {
        return <<<'BLADE'
<div class="report-header-default">
    <div>
        <strong>{{ $context['shop_name'] ?? $context['platform'] ?? config('app.name') }}</strong>
        <div class="muted">{{ $profile['label'] ?? $title }}</div>
    </div>
    <div class="report-date">{{ $generated_at->format('Y-m-d H:i') }}</div>
</div>
BLADE;
    }

    private function defaultFooter(): string
    {
        return <<<'BLADE'
<div class="report-footer-default">
    <span>{{ config('app.name') }}</span>
    <span>{{ $rows_count }} rows</span>
</div>
BLADE;
    }

    private function defaultBody(): string
    {
        return <<<'BLADE'
<table class="report-table">
    <thead>
        <tr>
            @foreach ($headers as $header)
                <th>{{ $header }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                @foreach ($headers as $header)
                    <td>{{ data_get($row, $header) }}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ max(count($headers), 1) }}">Sem dados para apresentar.</td>
            </tr>
        @endforelse
    </tbody>
</table>
BLADE;
    }

    private function defaultCss(): string
    {
        return <<<'CSS'
body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #1f2937; margin: 0; }
.report-shell { padding: 24px; }
.report-header-default { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 18px; }
.report-footer-default { display: flex; justify-content: space-between; border-top: 1px solid #d1d5db; padding-top: 10px; margin-top: 18px; font-size: 10px; color: #6b7280; }
.report-date, .muted { color: #6b7280; font-size: 11px; }
.report-table { width: 100%; border-collapse: collapse; }
.report-table th { background: #f3f4f6; text-align: left; font-weight: 700; }
.report-table th, .report-table td { border: 1px solid #e5e7eb; padding: 6px 8px; vertical-align: top; }
CSS;
    }
}
