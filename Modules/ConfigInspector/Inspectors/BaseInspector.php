<?php

namespace Modules\ConfigInspector\Inspectors;

use Modules\ConfigInspector\DTOs\InspectionItem;

abstract class BaseInspector
{
    abstract public function key(): string;
    abstract public function label(): string;
    abstract public function inspect(): array;

    protected function item(string $severity, string $title, string $message, array $meta = [], ?string $suggestion = null): array
    {
        return (new InspectionItem($severity, $title, $message, $meta, $suggestion))->toArray();
    }

    protected function exists(string $path): bool
    {
        return file_exists($path);
    }

    protected function mask(?string $value): string
    {
        if ($value === null || $value === '') {
            return 'not configured';
        }
        if (strlen($value) <= 6) {
            return str_repeat('*', strlen($value));
        }
        return substr($value, 0, 2) . str_repeat('*', max(strlen($value) - 4, 4)) . substr($value, -2);
    }
}
