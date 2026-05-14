<?php

namespace Modules\DataExportCenter\Services;

use RuntimeException;

class SelectOnlySqlGuard
{
    public function assertSelectOnly(string $sql): string
    {
        $sql = trim($sql);

        if ($sql === '') {
            throw new RuntimeException('Export SQL cannot be empty.');
        }

        $clean = $this->removeComments($sql);
        $statement = trim($clean);

        if (config('data-export-center.sql.forbid_multiple_statements', true) && $this->hasMultipleStatements($statement)) {
            throw new RuntimeException('Only one SELECT statement is allowed.');
        }

        $statement = rtrim($statement, "; \t\n\r\0\x0B");

        if (! preg_match('/^\s*(select|with)\b/i', $statement)) {
            throw new RuntimeException('Only SELECT queries are allowed for export SQL profiles.');
        }

        if (preg_match($this->forbiddenPattern(), $statement, $matches)) {
            throw new RuntimeException('Forbidden SQL keyword detected in export query: ' . strtoupper($matches[1]));
        }

        return rtrim($sql, "; \t\n\r\0\x0B");
    }

    public function withLimit(string $sql, int $limit): string
    {
        $sql = $this->assertSelectOnly($sql);

        if ($limit <= 0) {
            return $sql;
        }

        if (! config('data-export-center.sql.append_limit_when_missing', true)) {
            return $sql;
        }

        if (preg_match('/\blimit\s+\d+\s*$/i', $sql)) {
            return $sql;
        }

        return 'select * from (' . $sql . ') as export_center_source limit ' . $limit;
    }

    private function removeComments(string $sql): string
    {
        $sql = preg_replace('/\/\*.*?\*\//s', ' ', $sql) ?: $sql;
        $sql = preg_replace('/--.*?(\r\n|\r|\n|$)/', ' ', $sql) ?: $sql;

        return $sql;
    }

    private function hasMultipleStatements(string $sql): bool
    {
        $trimmed = trim($sql);
        $withoutTrailing = rtrim($trimmed, "; \t\n\r\0\x0B");

        return str_contains($withoutTrailing, ';');
    }

    private function forbiddenPattern(): string
    {
        $keywords = config('data-export-center.sql.forbidden_keywords', []);
        $escaped = array_map(fn ($keyword) => preg_quote((string) $keyword, '/'), $keywords);

        return '/\b(' . implode('|', $escaped) . ')\b/i';
    }
}
