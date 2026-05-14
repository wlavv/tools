<?php

namespace Modules\TranslationManager\Services;

class ArrayDotService
{
    public function flatten(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $result += $this->flatten($value, $newKey);
                continue;
            }

            $result[$newKey] = $value;
        }

        return $result;
    }

    public function unflatten(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $segments = explode('.', (string) $key);
            $target = &$result;

            foreach ($segments as $segment) {
                if (! isset($target[$segment]) || ! is_array($target[$segment])) {
                    $target[$segment] = [];
                }
                $target = &$target[$segment];
            }

            $target = $value;
            unset($target);
        }

        return $result;
    }
}
