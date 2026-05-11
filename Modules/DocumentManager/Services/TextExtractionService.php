<?php

namespace Modules\DocumentManager\Services;

use Illuminate\Support\Facades\Storage;
use Modules\DocumentManager\Models\DocumentVersion;
use Modules\DocumentManager\Support\DocumentLogger;

class TextExtractionService
{
    public function extract(DocumentVersion $version, int $maxChars = 30000): array
    {
        try {
            $mimeType = (string) $version->mime_type;
            $extension = strtolower((string) $version->extension);
            $path = $this->localPath($version);

            if (!$path || !is_file($path)) {
                return $this->result('', 'unavailable', 'Ficheiro indisponivel no storage.');
            }

            $text = match (true) {
                in_array($extension, ['txt', 'md', 'csv', 'json', 'xml', 'log', 'yaml', 'yml'], true) => $this->readPlainTextFile($path),
                in_array($extension, ['html', 'htm'], true) || $mimeType === 'text/html' => strip_tags($this->readPlainTextFile($path)),
                $extension === 'docx' => $this->readDocx($path),
                $extension === 'pdf' || $mimeType === 'application/pdf' => $this->readPdfBestEffort($path),
                str_starts_with($mimeType, 'text/') => $this->readPlainTextFile($path),
                default => '',
            };

            $text = $this->limitExtract($this->normalizeText($text), $maxChars);

            return $this->result(
                $text,
                $text !== '' ? 'completed' : 'empty',
                $text !== '' ? null : 'Nao foi possivel extrair texto neste formato sem provider OCR externo.'
            );
        } catch (\Throwable $e) {
            DocumentLogger::exception($e, ['version_id' => $version->id]);

            return $this->result('', 'failed', $e->getMessage());
        }
    }

    private function localPath(DocumentVersion $version): ?string
    {
        $disk = Storage::disk($version->disk);

        if (!$disk->exists($version->path)) {
            return null;
        }

        try {
            return $disk->path($version->path);
        } catch (\Throwable $e) {
            $stream = $disk->readStream($version->path);

            if (!$stream) {
                return null;
            }

            $tempPath = tempnam(sys_get_temp_dir(), 'dms_extract_');
            file_put_contents($tempPath, stream_get_contents($stream));

            if (is_resource($stream)) {
                fclose($stream);
            }

            return $tempPath;
        }
    }

    private function readPlainTextFile(string $path): string
    {
        $content = @file_get_contents($path);

        if ($content === false) {
            return '';
        }

        return trim(mb_convert_encoding($content, 'UTF-8', 'UTF-8'));
    }

    private function readDocx(string $path): string
    {
        if (!class_exists(\ZipArchive::class)) {
            return '';
        }

        $zip = new \ZipArchive();

        if ($zip->open($path) !== true) {
            return '';
        }

        $index = $zip->locateName('word/document.xml');

        if ($index === false) {
            $zip->close();
            return '';
        }

        $xml = $zip->getFromIndex($index);
        $zip->close();

        if ($xml === false) {
            return '';
        }

        $text = strip_tags(str_replace(['</w:p>', '</w:tr>'], ["\n", "\n"], $xml));

        return trim(html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8'));
    }

    private function readPdfBestEffort(string $path): string
    {
        $content = @file_get_contents($path);

        if ($content === false) {
            return '';
        }

        preg_match_all('/\(([^()]*(?:\\\\.[^()]*)*)\)/s', $content, $matches);

        $texts = array_map(function ($item) {
            $item = preg_replace('/\\\\([nrtbf()\\\\])/', ' ', $item);
            $item = preg_replace('/\\\\\d{3}/', ' ', $item);

            return trim($item);
        }, $matches[1] ?? []);

        return trim(implode(' ', array_filter($texts)));
    }

    private function normalizeText(string $text): string
    {
        return trim((string) preg_replace('/\s+/', ' ', $text));
    }

    private function limitExtract(string $text, int $maxChars): string
    {
        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        return mb_substr($text, 0, $maxChars) . "\n\n[conteudo truncado]";
    }

    private function result(string $text, string $status, ?string $message = null): array
    {
        return [
            'text' => $text,
            'status' => $status,
            'message' => $message,
            'confidence' => $text !== '' ? 0.7500 : null,
        ];
    }
}
