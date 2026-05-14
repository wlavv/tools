<?php

namespace Modules\DataExportCenter\Traits;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

trait HasExportContract
{
    public static function exportKey(): string
    {
        if (method_exists(static::class, 'importKey')) {
            return static::importKey();
        }

        return Str::snake(class_basename(static::class));
    }

    public static function exportLabel(): string
    {
        if (method_exists(static::class, 'importLabel')) {
            return static::importLabel();
        }

        return Str::headline(class_basename(static::class));
    }

    public static function exportFields(): array
    {
        if (method_exists(static::class, 'importFields')) {
            return static::importFields();
        }

        return [];
    }

    public static function exportDependencies(): array
    {
        if (method_exists(static::class, 'importDependencies')) {
            return static::importDependencies();
        }

        return [];
    }

    public static function exportFilters(): array
    {
        return [];
    }

    public static function modifyExportQuery(Builder $query, array $context = [], array $schema = []): Builder
    {
        return $query;
    }
}
