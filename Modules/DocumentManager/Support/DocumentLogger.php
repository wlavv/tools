<?php

namespace Modules\DocumentManager\Support;

class DocumentLogger
{
    public static function error(string $message, array $context = []): void
    {
        self::write('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('WARNING', $message, $context);
    }

    public static function info(string $message, array $context = []): void
    {
        self::write('INFO', $message, $context);
    }

    public static function exception(\Throwable $exception, array $context = []): void
    {
        self::error($exception->getMessage(), array_merge($context, [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => collect($exception->getTrace())->take(12)->toArray(),
        ]));
    }

    private static function write(string $level, string $message, array $context = []): void
    {
        try {
            $dir = storage_path('logs');

            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $line = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ' . $message;

            if (!empty($context)) {
                $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

            @file_put_contents($dir . DIRECTORY_SEPARATOR . 'document-manager.log', $line . PHP_EOL, FILE_APPEND);
        } catch (\Throwable $e) {
            // Logging must never break the back office.
        }
    }
}
