<?php

namespace Modules\PackageTracker\Support;

class JsonSanitizer
{
    public static function clean(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $json = json_encode($value, JSON_INVALID_UTF8_SUBSTITUTE);

        if ($json === false) {
            return null;
        }

        return json_decode($json, true);
    }

    public static function encode(mixed $value): ?string
    {
        $clean = self::clean($value);

        if ($clean === null) {
            return null;
        }

        $json = json_encode($clean, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        return $json === false ? null : $json;
    }
}
