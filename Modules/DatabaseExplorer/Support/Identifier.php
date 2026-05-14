<?php

namespace Modules\DatabaseExplorer\Support;

use InvalidArgumentException;

class Identifier
{
    public static function assertSafe(string $value, string $label = 'identifier'): void
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            throw new InvalidArgumentException("Invalid {$label}: {$value}");
        }
    }
}
