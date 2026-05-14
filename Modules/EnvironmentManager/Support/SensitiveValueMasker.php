<?php

namespace Modules\EnvironmentManager\Support;

use Illuminate\Support\Str;

class SensitiveValueMasker
{
    public function isSensitive(string $key, mixed $value = null, ?bool $forced = null): bool
    {
        if ($forced !== null) {
            return $forced;
        }

        $patterns = (array) config('environment-manager.sensitive_patterns', []);
        $normalizedKey = Str::upper(str_replace(['-', '.', ' ', ':', '/'], '_', $key));

        foreach ($patterns as $pattern) {
            $normalizedPattern = Str::upper(str_replace(['-', '.', ' ', ':', '/'], '_', (string) $pattern));

            if ($normalizedPattern !== '' && str_contains($normalizedKey, $normalizedPattern)) {
                return true;
            }
        }

        return false;
    }

    public function displayValue(string $key, mixed $value, ?bool $forcedSensitive = null): string
    {
        if ($this->shouldMask($key, $value, $forcedSensitive)) {
            return filled($value) ? '********' : '';
        }

        return ValueFormatter::format($value);
    }

    public function shouldMask(string $key, mixed $value = null, ?bool $forcedSensitive = null): bool
    {
        if (! (bool) config('environment-manager.mask_sensitive_values', true)) {
            return false;
        }

        return $this->isSensitive($key, $value, $forcedSensitive);
    }

    public function entry(string $key, mixed $value, string $source, array $extra = []): array
    {
        $forcedSensitive = array_key_exists('sensitive', $extra) && $extra['sensitive'] !== null ? (bool) $extra['sensitive'] : null;
        $sensitive = $this->isSensitive($key, $value, $forcedSensitive);

        return array_merge([
            'key' => $key,
            'value' => $this->displayValue($key, $value, $forcedSensitive),
            'source' => $source,
            'type' => ValueFormatter::typeOf($value),
            'sensitive' => $sensitive,
            'has_value' => filled($value),
            'readonly' => true,
        ], $extra, [
            'sensitive' => $sensitive,
            'value' => $this->displayValue($key, $value, $forcedSensitive),
        ]);
    }
}
