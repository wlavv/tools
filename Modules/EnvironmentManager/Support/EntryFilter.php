<?php

namespace Modules\EnvironmentManager\Support;

use Illuminate\Support\Str;

class EntryFilter
{
    public static function matches(array $entry, ?string $search): bool
    {
        $search = trim((string) $search);

        if ($search === '') {
            return true;
        }

        $haystack = Str::lower(json_encode($entry, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');

        return str_contains($haystack, Str::lower($search));
    }

    public static function filter(array $entries, ?string $search): array
    {
        return array_values(array_filter($entries, fn (array $entry) => self::matches($entry, $search)));
    }
}
