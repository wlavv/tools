<?php

namespace Modules\EnvironmentManager\Support;

use Illuminate\Support\Arr;

class ArrayTools
{
    public static function dotPreserveEmpty(array $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            $key = $prepend . $key;

            if (is_array($value) && ! empty($value)) {
                $results = array_merge($results, self::dotPreserveEmpty($value, $key . '.'));
            } else {
                $results[$key] = $value;
            }
        }

        return $results ?: Arr::dot($array);
    }
}
