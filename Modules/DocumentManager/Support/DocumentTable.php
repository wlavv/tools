<?php

namespace Modules\DocumentManager\Support;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DocumentTable
{
    public static function expectedTables(): array
    {
        return config('documentmanager.tables', []);
    }

    public static function exists(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['table' => $table]);

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
            DocumentLogger::exception($e, ['table' => $table]);

            return 0;
        }
    }

    public static function safeGet(string $table, ?callable $callback = null)
    {
        if (!self::exists($table)) {
            return collect();
        }

        try {
            $query = DB::table($table);

            if ($callback) {
                $callback($query);
            }

            return $query->get();
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['table' => $table]);

            return collect();
        }
    }

    public static function emptyPaginator(int $perPage = 25): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            collect(),
            0,
            $perPage,
            1,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public static function missingTables(): array
    {
        return array_values(array_filter(self::expectedTables(), function ($table) {
            return !self::exists($table);
        }));
    }
}
