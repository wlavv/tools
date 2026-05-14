<?php

namespace Modules\ErrorCenter\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class ErrorHashGenerator
{
    public function generate(Throwable $throwable, array $context = []): string
    {
        $environment = (string) Arr::get($context, 'environment', app()->environment());
        $source = (string) Arr::get($context, 'source', 'backend');
        $module = (string) Arr::get($context, 'module', 'unknown');
        $errorType = get_class($throwable);
        $message = $this->normalizeMessage($throwable->getMessage());
        $stackOrigin = $this->extractStackOrigin($throwable);

        $raw = implode('|', [
            $environment,
            $source,
            $module,
            $errorType,
            $message,
            $stackOrigin,
        ]);

        return hash('sha256', $raw);
    }

    public function normalizeMessage(?string $message): string
    {
        $message = trim((string) $message);

        if ($message === '') {
            return '';
        }

        $patterns = [
            '/[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}/i' => '{uuid}',
            '/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i' => '{email}',
            '/\b\d{4}-\d{2}-\d{2}(?:[ T]\d{2}:\d{2}:\d{2}(?:\.\d+)?Z?)?\b/' => '{date}',
            '/\?.*/' => '?{query}',
            '/\b\d{5,}\b/' => '{number}',
            '/\b\d+\b/' => '{number}',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $message = preg_replace($pattern, $replacement, $message) ?? $message;
        }

        return trim(preg_replace('/\s+/', ' ', $message) ?? $message);
    }

    public function extractStackOrigin(Throwable $throwable): string
    {
        foreach ($throwable->getTrace() as $frame) {
            $file = (string) Arr::get($frame, 'file', '');
            $function = (string) Arr::get($frame, 'function', 'unknown');
            $class = (string) Arr::get($frame, 'class', '');

            if ($file === '') {
                continue;
            }

            if (Str::contains($file, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR)) {
                continue;
            }

            if (Str::contains($class, 'Modules\\ErrorCenter\\')) {
                continue;
            }

            return $this->compactPath($file) . ':' . $function;
        }

        $file = $throwable->getFile();
        $function = 'line_' . $throwable->getLine();

        return $file ? $this->compactPath($file) . ':' . $function : 'unknown';
    }

    private function compactPath(string $file): string
    {
        $basePath = base_path();

        if (Str::startsWith($file, $basePath)) {
            return ltrim(Str::after($file, $basePath), DIRECTORY_SEPARATOR);
        }

        return $file;
    }
}
