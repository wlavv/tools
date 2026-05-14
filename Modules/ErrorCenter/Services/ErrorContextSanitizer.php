<?php

namespace Modules\ErrorCenter\Services;

use Illuminate\Support\Str;

class ErrorContextSanitizer
{
    public function sanitize(mixed $value, int $depth = 0): mixed
    {
        $maxDepth = (int) config('error-center.sanitizer.max_depth', 8);

        if ($depth > $maxDepth) {
            return '[MAX_DEPTH_REACHED]';
        }

        if ($value === null || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return $this->sanitizeString($value);
        }

        if ($value instanceof \JsonSerializable) {
            return $this->sanitize($value->jsonSerialize(), $depth + 1);
        }

        if ($value instanceof \Stringable) {
            return $this->sanitizeString((string) $value);
        }

        if (is_array($value)) {
            $sanitized = [];

            foreach ($value as $key => $item) {
                if ($this->isSensitiveKey((string) $key)) {
                    $sanitized[$key] = $this->redactedValue();
                    continue;
                }

                $sanitized[$key] = $this->sanitize($item, $depth + 1);
            }

            return $sanitized;
        }

        if (is_object($value)) {
            return $this->sanitize((array) $value, $depth + 1);
        }

        return $value;
    }

    public function isSensitiveKey(string $key): bool
    {
        $normalized = Str::lower($key);
        $sensitiveKeys = config('error-center.sanitizer.sensitive_keys', []);

        foreach ($sensitiveKeys as $sensitiveKey) {
            if (Str::contains($normalized, Str::lower((string) $sensitiveKey))) {
                return true;
            }
        }

        return false;
    }

    private function sanitizeString(string $value): string
    {
        $maxLength = (int) config('error-center.sanitizer.max_string_length', 10000);

        if (Str::length($value) <= $maxLength) {
            return $value;
        }

        return Str::limit($value, $maxLength, '...[TRUNCATED]');
    }

    private function redactedValue(): string
    {
        return (string) config('error-center.sanitizer.redacted_value', '[REDACTED]');
    }
}
