<?php

namespace Modules\DataImportWizard\Traits;

use Illuminate\Support\Str;

trait HasImportContract
{
    public static function importKey(): string
    {
        return Str::snake(class_basename(static::class));
    }

    public static function importLabel(): string
    {
        return class_basename(static::class);
    }

    public static function importFields(): array
    {
        return [];
    }

    public static function importRules(): array
    {
        return [];
    }

    public static function importDependencies(): array
    {
        return [];
    }

    /**
     * Import field keys used to locate an existing record.
     * Example: ['reference']
     */
    public static function importLookupColumns(): array
    {
        $lookup = [];

        foreach (static::importFields() as $field => $definition) {
            if (($definition['lookup'] ?? false) === true) {
                $lookup[] = $field;
            }
        }

        return $lookup;
    }

    /**
     * Backwards-compatible alias for lookup fields.
     */
    public static function importUniqueBy(): array
    {
        return static::importLookupColumns();
    }
}
