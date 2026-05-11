<?php

namespace Modules\CatalogManager\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CatalogTable
{
    public static function expectedTables(): array
    {
        return config('catalogmanager.tables', []);
    }

    public static function exists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function count(string $table, ?callable $callback = null): int
    {
        if (!self::exists($table)) {
            return 0;
        }

        try {
            $query = DB::table($table);

            if ($callback) {
                $callback($query);
            }

            return (int) $query->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    public static function emptyPaginator(int $perPage = 25)
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public static function emptyCollection()
    {
        return collect();
    }

    public static function safeGet(string $table, ?callable $callback = null)
    {
        if (!self::exists($table)) {
            return self::emptyCollection();
        }

        try {
            $query = DB::table($table);

            if ($callback) {
                $callback($query);
            }

            return $query->get();
        } catch (\Throwable $e) {
            CatalogLogger::exception($e, ['table' => $table]);

            return self::emptyCollection();
        }
    }
}
