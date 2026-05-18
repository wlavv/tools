<?php

namespace Modules\PackageTracker\Services\Carriers\Support;

class CarrierPayloadReader
{
    public static function first(array $payload, array $paths, mixed $default = null): mixed
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }

    public static function list(array $payload, array $paths): array
    {
        foreach ($paths as $path) {
            $value = data_get($payload, $path);
            if (is_array($value)) {
                return array_is_list($value) ? $value : [$value];
            }
        }

        return [];
    }
}
