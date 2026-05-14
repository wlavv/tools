<?php

namespace Modules\ConfigInspector\Services;

use Throwable;

class ConfigInspectorService
{
    public function inspectors(): array
    {
        return config('config-inspector.inspectors', []);
    }

    public function run(?string $active = null): array
    {
        $inspectors = $this->inspectors();
        $active = $active ?: array_key_first($inspectors);

        $results = [];
        foreach ($inspectors as $key => $definition) {
            $class = $definition['class'] ?? null;
            $results[$key] = [
                'key' => $key,
                'label' => $definition['label'] ?? $key,
                'icon' => $definition['icon'] ?? 'fa-solid fa-circle-info',
                'description' => $definition['description'] ?? '',
                'items' => [],
                'summary' => $this->emptySummary(),
            ];

            if (!$class || !class_exists($class)) {
                $results[$key]['items'][] = [
                    'severity' => 'error',
                    'title' => 'Inspector unavailable',
                    'message' => 'Inspector class not found: ' . ($class ?: 'undefined'),
                    'meta' => [],
                    'suggestion' => 'Confirmar Config/config.php do módulo ConfigInspector.',
                ];
                $results[$key]['summary'] = $this->summarize($results[$key]['items']);
                continue;
            }

            try {
                $instance = app($class);
                $items = $instance->inspect();
                $results[$key]['items'] = $items;
                $results[$key]['summary'] = $this->summarize($items);
            } catch (Throwable $e) {
                $results[$key]['items'][] = [
                    'severity' => 'critical',
                    'title' => 'Inspector failed',
                    'message' => $e->getMessage(),
                    'meta' => ['class' => $class],
                    'suggestion' => 'Verificar logs e dependências do inspector.',
                ];
                $results[$key]['summary'] = $this->summarize($results[$key]['items']);
            }
        }

        return [
            'active' => $active,
            'inspectors' => $inspectors,
            'results' => $results,
            'global' => $this->globalSummary($results),
        ];
    }

    protected function emptySummary(): array
    {
        return ['critical' => 0, 'error' => 0, 'warning' => 0, 'info' => 0, 'success' => 0, 'total' => 0, 'score' => 100];
    }

    protected function summarize(array $items): array
    {
        $summary = $this->emptySummary();
        foreach ($items as $item) {
            $severity = $item['severity'] ?? 'info';
            if (!array_key_exists($severity, $summary)) {
                $severity = 'info';
            }
            $summary[$severity]++;
            $summary['total']++;
        }

        $summary['score'] = $this->healthScore($summary);

        return $summary;
    }

    protected function healthScore(array $summary): int
    {
        $total = max(1, (int) ($summary['total'] ?? 0));

        $weightedScore =
            ((int) ($summary['critical'] ?? 0) * 0) +
            ((int) ($summary['error'] ?? 0) * 20) +
            ((int) ($summary['warning'] ?? 0) * 60) +
            ((int) ($summary['info'] ?? 0) * 85) +
            ((int) ($summary['success'] ?? 0) * 100);

        return (int) round($weightedScore / $total);
    }

    protected function globalSummary(array $results): array
    {
        $items = [];
        foreach ($results as $result) {
            foreach (($result['items'] ?? []) as $item) {
                $items[] = $item;
            }
        }
        return $this->summarize($items);
    }
}
