<?php

namespace Modules\EnvironmentManager\Support;

use Illuminate\Support\Str;
use Stringable;

class ValueFormatter
{
    public static function format(mixed $value, int $limit = 500): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_scalar($value)) {
            return Str::limit((string) $value, $limit);
        }

        if ($value instanceof Stringable) {
            return Str::limit((string) $value, $limit);
        }

        if ($value instanceof \Closure) {
            return '[closure]';
        }

        if (is_object($value)) {
            return '[object ' . get_class($value) . ']';
        }

        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return Str::limit($json !== false ? $json : '[' . gettype($value) . ']', $limit);
    }

    public static function typeOf(mixed $value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_object($value)) {
            return get_class($value);
        }

        return gettype($value);
    }
}
