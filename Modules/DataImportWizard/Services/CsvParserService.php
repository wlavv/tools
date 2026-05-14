<?php

namespace Modules\DataImportWizard\Services;

use RuntimeException;

class CsvParserService
{
    public function parse(string $path): array
    {
        if (! is_readable($path)) {
            throw new RuntimeException("CSV file [{$path}] is not readable.");
        }

        $delimiter = config('data-import-wizard.csv.delimiter') ?: $this->detectDelimiter($path);
        $handle = fopen($path, 'rb');

        if (! $handle) {
            throw new RuntimeException("Unable to open CSV file [{$path}].");
        }

        $headers = fgetcsv($handle, 0, $delimiter);

        if (! is_array($headers)) {
            throw new RuntimeException('CSV file does not contain a header row.');
        }

        $headers = array_map(fn ($value) => $this->cleanHeader((string) $value), $headers);

        if (count($headers) !== count(array_unique($headers))) {
            throw new RuntimeException('CSV file contains duplicated headers.');
        }

        $rows = [];
        $rowNumber = 1;
        $maxRows = (int) config('data-import-wizard.csv.max_rows', 10000);

        while (($values = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;

            if ($this->isEmptyRow($values)) {
                continue;
            }

            if (count($rows) >= $maxRows) {
                throw new RuntimeException("CSV exceeds configured max rows limit [{$maxRows}].");
            }

            $values = array_pad($values, count($headers), null);
            $values = array_slice($values, 0, count($headers));

            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = $this->normalizeValue($values[$index] ?? null);
            }

            $rows[] = [
                'row_number' => $rowNumber,
                'data' => $row,
            ];
        }

        fclose($handle);

        return [
            'delimiter' => $delimiter,
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    private function detectDelimiter(string $path): string
    {
        $line = (string) fgets(fopen($path, 'rb'));

        $candidates = [',' => 0, ';' => 0, "\t" => 0];

        foreach ($candidates as $delimiter => $count) {
            $candidates[$delimiter] = substr_count($line, $delimiter);
        }

        arsort($candidates);

        return array_key_first($candidates) ?: ',';
    }

    private function cleanHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?: $header;

        return trim($header);
    }

    private function normalizeValue(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
