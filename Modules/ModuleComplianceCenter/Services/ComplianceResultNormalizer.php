<?php

namespace Modules\ModuleComplianceCenter\Services;

use Throwable;

class ComplianceResultNormalizer
{
    public function normalize(mixed $result, string $validatorKey): array
    {
        $payload = is_object($result) && method_exists($result, 'toArray') ? $result->toArray() : (array) $result;
        $findings = collect($payload['findings'] ?? [])->map(fn ($finding) => $this->normalizeFinding((array) $finding, $validatorKey, $payload['area'] ?? $validatorKey))->all();

        return [
            'validator' => $payload['validator'] ?? $validatorKey,
            'area' => $payload['area'] ?? $validatorKey,
            'label' => $payload['label'] ?? $validatorKey,
            'status' => $payload['status'] ?? 'manual_review_required',
            'score' => (float) ($payload['score'] ?? 0),
            'findings' => $findings,
            'raw' => $payload,
        ];
    }

    public function unavailable(array $validator): array
    {
        return [
            'validator' => $validator['key'],
            'area' => $validator['key'],
            'label' => $validator['label'],
            'status' => 'unavailable',
            'score' => null,
            'findings' => [[
                'validator_key' => $validator['key'],
                'area' => $validator['key'],
                'code' => 'VALIDATOR_UNAVAILABLE',
                'status' => 'skipped',
                'severity' => 'medium',
                'title' => 'Validator unavailable',
                'message' => 'Validator service is not installed or not available.',
                'recommendation' => 'Install or enable ' . $validator['module'] . ' before requiring this compliance check.',
            ]],
            'raw' => ['unavailable' => true],
        ];
    }

    public function exception(array $validator, Throwable $exception): array
    {
        return [
            'validator' => $validator['key'],
            'area' => $validator['key'],
            'label' => $validator['label'],
            'status' => 'error',
            'score' => 0,
            'findings' => [[
                'validator_key' => $validator['key'],
                'area' => $validator['key'],
                'code' => 'VALIDATOR_EXECUTION_ERROR',
                'status' => 'failed',
                'severity' => 'high',
                'title' => 'Validator execution error',
                'message' => $exception->getMessage(),
                'recommendation' => 'Review the validator service and rerun compliance.',
            ]],
            'raw' => ['exception' => get_class($exception), 'message' => $exception->getMessage()],
        ];
    }

    private function normalizeFinding(array $finding, string $validatorKey, string $area): array
    {
        return [
            'validator_key' => $validatorKey,
            'area' => $finding['area'] ?? $area,
            'code' => (string) ($finding['code'] ?? 'UNKNOWN_FINDING'),
            'status' => (string) ($finding['status'] ?? 'manual_review_required'),
            'severity' => (string) ($finding['severity'] ?? 'medium'),
            'title' => (string) ($finding['title'] ?? 'Compliance finding'),
            'message' => $finding['message'] ?? null,
            'file_path' => $finding['file_path'] ?? null,
            'line_number' => $finding['line_number'] ?? null,
            'expected_value' => $this->stringify($finding['expected_value'] ?? null),
            'actual_value' => $this->stringify($finding['actual_value'] ?? null),
            'recommendation' => $finding['recommendation'] ?? null,
            'auto_fix_available' => (bool) ($finding['auto_fix_available'] ?? false),
            'metadata' => $finding['metadata'] ?? null,
        ];
    }

    private function stringify(mixed $value): ?string
    {
        if ($value === null || is_string($value)) {
            return $value;
        }

        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
