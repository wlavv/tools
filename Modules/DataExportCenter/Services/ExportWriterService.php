<?php

namespace Modules\DataExportCenter\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Traversable;

class ExportWriterService
{
    public function write(iterable $rows, string $format, string $disk, string $path, array $headers = []): array
    {
        return match ($format) {
            'csv' => $this->writeCsv($rows, $disk, $path, $headers),
            'json' => $this->writeJson($rows, $disk, $path, $headers),
            default => throw new RuntimeException("Unsupported writer format [{$format}]."),
        };
    }

    private function writeCsv(iterable $rows, string $disk, string $path, array $headers): array
    {
        $temp = tempnam(sys_get_temp_dir(), 'data_export_center_');
        $handle = fopen($temp, 'w+');

        if (! $handle) {
            throw new RuntimeException('Unable to create export temporary file.');
        }

        if (config('data-export-center.csv.include_bom', true)) {
            fwrite($handle, "\xEF\xBB\xBF");
        }

        $delimiter = config('data-export-center.csv.delimiter', ';');
        $enclosure = config('data-export-center.csv.enclosure', '"');
        $escape = config('data-export-center.csv.escape', '\\');
        $count = 0;
        $headersWritten = false;

        foreach ($rows as $row) {
            $data = $this->rowToArray($row);

            if (empty($headers)) {
                $headers = array_keys($data);
            }

            if (! $headersWritten) {
                fputcsv($handle, $headers, $delimiter, $enclosure, $escape);
                $headersWritten = true;
            }

            fputcsv($handle, array_map(fn ($header) => $data[$header] ?? null, $headers), $delimiter, $enclosure, $escape);
            $count++;
        }

        if (! $headersWritten && ! empty($headers)) {
            fputcsv($handle, $headers, $delimiter, $enclosure, $escape);
        }

        rewind($handle);
        Storage::disk($disk)->put($path, $handle);
        fclose($handle);
        @unlink($temp);

        return ['rows_count' => $count, 'headers' => $headers];
    }

    private function writeJson(iterable $rows, string $disk, string $path, array $headers): array
    {
        $temp = tempnam(sys_get_temp_dir(), 'data_export_center_');
        $handle = fopen($temp, 'w+');

        if (! $handle) {
            throw new RuntimeException('Unable to create export temporary file.');
        }

        fwrite($handle, '[');
        $count = 0;
        $first = true;

        foreach ($rows as $row) {
            $data = $this->rowToArray($row);
            if (! empty($headers)) {
                $data = collect($headers)->mapWithKeys(fn ($header) => [$header => $data[$header] ?? null])->all();
            }

            fwrite($handle, $first ? '' : ',');
            fwrite($handle, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $first = false;
            $count++;
        }

        fwrite($handle, ']');
        rewind($handle);
        Storage::disk($disk)->put($path, $handle);
        fclose($handle);
        @unlink($temp);

        return ['rows_count' => $count, 'headers' => $headers];
    }

    private function rowToArray(mixed $row): array
    {
        if (is_array($row)) {
            return $row;
        }

        if ($row instanceof Traversable) {
            return iterator_to_array($row);
        }

        return (array) $row;
    }
}
